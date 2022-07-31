<?php

namespace API\Helpers;

DEFINE("RMS_SALES", 1);
DEFINE("RMS_PURCHASE", 2);
DEFINE("RMS_CASH_MOVEMENT", 3);
DEFINE("RMS_CONTRACT", 4);

class Helper
{
    /*
     * description : function to create the api response standard format
     * input: status code , data , error message
     * output: api response in standard format
     * Example: api_format(['message' => 100] , 401 , 'Invalid Username or password');
     */
    public static function api_format($data, $status_code = 200, $error_message = null)
    {
        //response return
        header('Content-type:application/json;charset=utf-8');
        // return the response with specific code like 401 , 200
        http_response_code($status_code);
        //the structure of the response
        $data = (object)[
            'data' => (object)$data,
            'message' => $error_message
        ];

        return json_encode($data);
    }

    /*
     * description: taking string and convert it to associative array
     * input: string object
     * output: associative array
     */
    public static function convert_to_array($string_data)
    {
        //decode it back to json
        $string_data = json_decode($string_data);

        // convert it to associative array
        return (array)$string_data;
    }

    function user_numeric($input)
    {
        global $SysPrefs;
        $num = trim($input);
        $sep = $SysPrefs->thoseps[user_tho_sep()];
        if ($sep != '')
            $num = str_replace($sep, '', $num);

        $sep = $SysPrefs->decseps[user_dec_sep()];
        if ($sep != '.')
            $num = str_replace($sep, '.', $num);

        if (!is_numeric($num))
            return false;
        $num = (float)$num;
        if ($num == (int)$num)
            return (int)$num;
        else
            return $num;
    }

    public static function input_num_api($postname = null, $dflt = 0)
    {
        if (!isset($postname) || $postname == "")
            return $dflt;
        $object_from_helper = new Helper();
        return $object_from_helper->user_numeric($postname);
    }

    public static function check_num_api($postname, $min = null, $max = null, $dflt = 0)
    {
        if (!isset($postname))
            return 0;
        $num = Helper::input_num_api($postname, $dflt);
        if ($num === false || $num === null)
            return 0;
        if (isset($min) && ($num < $min))
            return 0;
        if (isset($max) && ($num > $max))
            return 0;
        return 1;
    }

    /*
     * description: set default values in passed object with the default values const
     * input : reference to an object ( &info) , key to the default values
     * output: none
     */
    public static function set_default_values(&$info, $key)
    {
        self::set_generalSetup();
        foreach ($GLOBALS['DEFAULT_VALUES'][$key] as $key => $DEFAULT_VALUE) {
            if (!isset($info[$key])) {
                $info[$key] = $DEFAULT_VALUE;
            }
        }
    }

    /**
     * set specific default values from the general setup of the current company
     * input: none
     * output: none
     */
    public static function set_generalSetup()
    {
        global $SysPrefs;
        $generalSetup = $SysPrefs->prefs;

        $GLOBALS['DEFAULT_VALUES']['supplier']['payable_account'] = $generalSetup['creditors_act'];
        $GLOBALS['DEFAULT_VALUES']['supplier']['payment_discount_account'] = $generalSetup['pyt_discount_act'];

    }

    /**
     * create limit query
     * @return string
     */
    public static function getLimit()
    {
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
        if (!isset($_GET['page'])) {
            return "";
        }
        $page = $_GET['page'] <= 0 ? 1 : $_GET['page'];
        // page 1 represent as 0 offset
        $page = ($page - 1) * $limit;
        return " LIMIT " . $page . "," . $limit;
    }

    /**
     * get previous and next url if exist
     * @param $query
     * @return array
     */
    public static function getNextAndPreviousUrl($query)
    {
        $page = isset($_GET['page']) ? $_GET['page'] : null;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;

        $prev_url = null;
        $next_url = null;
        if ($page)   // if page passed as parameter in params uri and limit also
        {
            $prev_url = $page <= 1 ? null : "?page=" . ($page - 1) . "&limit=" . $limit;
            $next_url = self::haveMoreData($query) ? "?page=" . ($page + 1) . "&limit=" . $limit : null;
            return ['previous_url' => $prev_url, 'next_url' => $next_url];
        }
        return [];
    }

    /**
     * check if the table we are selecting has more data , for next url creator
     * @param $query
     * @return bool
     */
    public static function haveMoreData($query)
    {
        $_GET['page']++;
        $db_object = db_query($query . " " . self::getLimit(), "error");
        $_GET['page']--;

        return mysqli_num_rows($db_object) > 0 ? true : false;
    }

    /**
     * Get Sort query part
     * @param null $sort_by
     * @param string $order
     * @return string
     */
    public static function getSortBy($sort_by = null, $order = "DESC")
    {
        $order = isset($_GET['order']) ? $_GET['order'] : $order;
        return $sort_by ? " ORDER BY $sort_by $order " : "";
    }

    /**
     * @param $searchable_columns array
     * @return mixed
     */
    public static function searchQuery($searchable_columns, $keyword=null)
    {
        $query = " AND (";
        $search_value = null;
        if($keyword) {
            $search_value = $keyword;
        } else {
            $search_value = $_GET['keyword'] ?? null;
        }
        if ($search_value) {
            reset($searchable_columns);
            $first_column = key($searchable_columns);
            if ($first_column) {
                $query .= self::getLikeOrEqualCompare($first_column, $search_value, $searchable_columns[$first_column]);
            }
            foreach ($searchable_columns as $column_name => $isLikeOrEqual) {
                if ($column_name !== $first_column) {
                    $query .= " OR " . self::getLikeOrEqualCompare($column_name, $search_value, $isLikeOrEqual);
                }
            }
            return $query .  ")";
        }
        return "";
    }

    
    public static function searchQueryForCalcField($Calculated_columns, $keyword=null)
    {
        $calc_value = null;
        $query = " OR ( ";
        $search_value = null;
        if($keyword) {
            $search_value = $keyword;
        } else {
            $search_value = $_GET['keyword'] ?? null;
        }
        $calcu_query = " ";       
        $search_value = str_replace(',',null,$search_value);        
        if (is_numeric($search_value)) {            
            foreach ($Calculated_columns as $column_name =>$value) {
                $calcu_query .= " " .$value." + "; 
            }
            $calcu_query =rtrim($calcu_query,' + ');            
            $query .= "  " .$calcu_query. " = ".$search_value;
            return $query . ")";
        }
        return "";
    }

    /**
     * get like or equal query
     * @param $column_name
     * @param $column_value
     * @param bool $isLike
     * @return string
     */
    public static function getLikeOrEqualCompare($column_name, $column_value, $isLike = true)
    {
        return $isLike ? " $column_name LIKE '%$column_value%'" : " $column_name='$column_value' ";
    }

    /**
     * convert array to string set
     * @param array $array
     * @param bool $withQuotes
     * @return string
     */
    public static function convertArrayToSetString(array $array, $withQuotes = true): string
    {
        $arraySet = "(";
        for ($index = 0; $index < count($array); $index++) {
            $arraySet .= $index ? "," : "";
            $arraySet = $withQuotes ? $arraySet . "'$array[$index]'" : $arraySet . "$array[$index]";
        }
        $arraySet .= ")";

        return $arraySet;
    }

    public static function getPostData()
    {
        $info = $_POST;
        return $info;
    }

    /*
     * count how many records exist for the passing table and condition part
     */
    public static function getCount($where_part)
    {
        return db_fetch_assoc(db_query("SELECT COUNT(*) as records_count {$where_part}"))['records_count'];
    }

    /**
     * convert the result query to associative array
     * @param $db_result
     * @return array
     */
    public static function getResultQueryAsArray($db_result): array
    {
        $result = [];
        while ($row = db_fetch_assoc($db_result)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * check if the given variable is number and between the target max and min
     * @param $variable
     * @param null $max
     * @param null $min
     * @return int
     */
    public static function isNumber($variable, $min = null, $max = null)
    {
        $num = user_numeric($variable);
        if ($num === false || $num === null)
            return 0;
        if (isset($min) && ($num < $min))
            return 0;
        if (isset($max) && ($num > $max))
            return 0;

        return 1;
    }

    public static function get_trans_type($type) 
    {
        switch ($type)
	    {
            case ST_SALESINVOICE:
                return 'Sales Invoice';
                break;
            case ST_SUPPINVOICE:
                return 'Purchase';
                break;
            case ST_SUPPAYMENT:
                return 'Supplier Payment';
                break; 
            case ST_BANKPAYMENT:
                return 'Bank Payment';
                break;
            case ST_BANKDEPOSIT:
                return 'Bank Deposit';
                break; 
            
            default:
                return '';
                break;    
        }
    }

    public static function getPublicURL($name)
    {
        global $SysPrefs;
        return $SysPrefs->ERP_BASE_PATH . "/public/$name";
    }

    public static function minusValue($value)
    {
        if($value < 0) return $value;
        return $value * -1;
    }

    public static function api_success_response($body, $message = '')
    {
        return json_encode(['code' => 200, 'message' => $message, 'data' => $body], JSON_INVALID_UTF8_IGNORE);
    }
}
