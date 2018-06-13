<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Integration\Baidu;

/**
 * @summary 百度地图WebAPI(Place检索接口未实现) <http://developer.baidu.com/map/index.php?title=webapi>
 * @author Raven <karascanvas@qq.com>
 * @package CommonLib\Baidu
 */
class BaiduMap
{
    const BASE_URI = 'http://api.map.baidu.com/';
    const API_SUGGESTION = 'place/v2/suggestion';
    const API_GEOCODER = 'geocoder/v2/';
    const API_DIRECTION = 'direction/v1';
    const API_ROUTEMATRIX = 'direction/v1/routematrix';
    const API_IPLOCATION = 'location/ip';
    const API_GEOCONV = 'geoconv/v1/';
    const API_PLACE_SEARCH = 'place/v2/search';
    const API_PLACE_DETAIL = 'place/v2/detail';
    const API_PLACE_EVENTSEARCH = 'place/v2/eventsearch';
    const API_PLACE_EVENTDETAIL = 'place/v2/eventdetail';
    const MODE_DRIVING = 'driving';
    const MODE_WALKING = 'walking';
    const MODE_TRANSIT = 'transit';
    const TACTIC_NO_EXPRESS = 10;
    const TACTIC_LEAST_TIME = 11;
    const TACTIC_SHORTEST_PATH = 12;
    const COORD_TYPE_BD09LL = 'bd09ll';
    const COORD_TYPE_BD09MC = 'bd09mc';
    const COORD_TYPE_GCJ02 = 'gcj02';
    const COORD_TYPE_WGS84 = 'wgs84';
    const POI_HIDE = 0;
    const POI_SHOW = 1;
    const PLACE_SCOPE_BASE = 1;
    const PLACE_SCOPE_DETAIL = 2;
    const OUTPUT = 'json';
    const SEPARATOR = '|';
    const DEFAULT_REGION = 'China';

    protected $apiKey;
    protected $securityKey;
    private $_signRequired;


    public function __construct($apiKey, $securityKey = null)
    {
        $this->apiKey = strval($apiKey);
        $this->securityKey = strval($securityKey);
        $this->_signRequired = ($securityKey !== null);
    }

    /**
     * 地址查询建议 <http://dwz.cn/QszjV>
     * @param string $query 输入建议关键字
     * @param string $region 所属城市/区域名称或代号
     * @param string $location 传入location参数后，返回结果将以距离进行排序
     * @return array
     */
    public function suggestion($query, $region = self::DEFAULT_REGION, $location = null)
    {
        $params = array(
            'query'  => $query,
            'region' => $region,
        );
        if ($location !== null) {
            $params['location'] = $location;
        }
        return $this->invoke(self::API_SUGGESTION, $params);
    }

    /**
     * 从地址到经纬度坐标转换 <http://dwz.cn/QtyuV>
     * @param string $address 查询地址
     * @param string $city 所属城市
     * @return array
     */
    public function geocoder($address, $city = null)
    {
        $params = array(
            'address' => $address,
        );
        if ($city !== null) {
            $params['city'] = $city;
        }
        return $this->invoke(self::API_GEOCODER, $params);
    }

    /**
     * 从经纬度到地址坐标转换 <http://dwz.cn/QtyuV>
     * @param string $lat 纬度
     * @param string $lng 经度
     * @param string $coordtype 坐标类型
     * @param int $pois 是否显示指定位置周边的poi (0:隐藏, 1:显示)
     * @return array
     */
    public function geocoderReverse($lat, $lng, $coordtype = null, $pois = null)
    {
        $params = array(
            'location' => $lat . ',' . $lng,
        );
        if ($coordtype !== null) {
            $params['coordtype'] = $coordtype;
        }
        if ($pois !== null) {
            $params['pois'] = $pois;
        }
        return $this->invoke(self::API_GEOCODER, $params);
    }

    /**
     * 导航 <http://dwz.cn/QtZWj>
     * @param string $origin 起点名称或经纬度
     * @param string $destination 终点名称或经纬度
     * @param string $region 公交、步行导航时该参数必填
     * @param string $origin_region 起始点所在城市，驾车导航时必填
     * @param string $destination_region 终点所在城市，驾车导航时必填
     * @param string $mode 导航模式
     * @param string $coord_type 坐标类型
     * @param int $tactics 导航策略
     * @param array $waypoints 经过地点,分隔的地址名称或经纬度
     * @return array
     */
    public function direction($origin, $destination, $region = null, $origin_region = null, $destination_region = null, $mode = null, $coord_type = null, $tactics = null, array $waypoints = array())
    {
        $params = array(
            'origin'             => $origin,
            'destination'        => $destination,
            'mode'               => $mode,
            'region'             => $region,
            'origin_region'      => $origin_region,
            'destination_region' => $destination_region,
            'coord_type'         => $coord_type,
            'tactics'            => $tactics,
        );
        if (!empty($waypoints)) {
            $params['waypoints'] = implode(self::SEPARATOR, (array)$waypoints);
        }
        return $this->invoke(self::API_DIRECTION, $params);
    }

    /**
     * 批量线路查询 <http://dwz.cn/QtUUW>
     * @param array|string $origins 起点坐标(集合)
     * @param array|string $destinations 目标坐标(集合)
     * @param string $mode 导航模式
     * @param string $coord_type 坐标类型
     * @param int $tactics 导航策略
     * @return array
     */
    public function routeMatrix($origins, $destinations, $mode = null, $coord_type = null, $tactics = null)
    {
        $params = array(
            'origins'      => is_array($origins) ? implode(self::SEPARATOR, $origins) : strval($origins),
            'destinations' => is_array($destinations) ? implode(self::SEPARATOR, $destinations) : strval($destinations),
            'mode'         => $mode,
            'coord_type'   => $coord_type,
            'tactics'      => $tactics,
        );
        return $this->invoke(self::API_ROUTEMATRIX, $params);
    }

    /**
     * IP定位 <http://dwz.cn/QtMvg>
     * @param string $ip 指定IP地址(如果不提供就采用本机的IP)
     * @param string $coord_type 坐标类型
     * @return array
     */
    public function ipLocation($ip = null, $coord_type = null)
    {
        $params = array(
            'ip'   => $ip,
            'coor' => $coord_type,
        );
        return $this->invoke(self::API_IPLOCATION, $params);
    }

    /**
     * 坐标转换 <http://dwz.cn/QtTgB>
     * @param string|array $coords 源坐标
     * @param int $from 源类型 (1-8)
     * @param int $to 目标类型 (5,6)
     * @return array
     */
    public function geoconv($coords, $from = null, $to = null)
    {
        $params = array(
            'coords' => is_array($coords) ? implode(';', $coords) : strval($coords),
            'from'   => $from,
            'to'     => $to,
        );
        return $this->invoke(self::API_GEOCONV, $params);
    }

    /**
     * Place区域检索POI服务 <http://dwz.cn/Quedb>
     * @return array
     */
    public function placeSearch()
    {
        // todo impl BaiduMap.placeSearch;
        $params = array();
        return $this->invoke(self::API_PLACE_SEARCH, $params);
    }

    /**
     * Place详情检索 <http://dwz.cn/QueQX>
     * @param string|array $uid poi的uid(或集合)
     * @param int $scope 检索结果详细程度 (1:基本, 2:详细)
     * @return array
     */
    public function placeDetail($uid, $scope = self::PLACE_SCOPE_BASE)
    {
        if (is_array($uid)) {
            $params['uids'] = implode(',', $uid);
        } elseif (strpos($uid, ',')) {
            $params['uids'] = $uid;
        } else {
            $params['uid'] = $uid;
        }
        $params['scope'] = $scope;
        return $this->invoke(self::API_PLACE_DETAIL, $params);
    }

    /**
     * Place团购信息检索服务 <http://dwz.cn/Quf9P>
     * @return array
     */
    public function placeEventSearch()
    {
        // todo impl BaiduMap.placeEventSearch;
        $params = array();
        return $this->invoke(self::API_PLACE_EVENTSEARCH, $params);
    }

    /**
     * Place商家团购详情查询 <http://dwz.cn/Quf9P>
     * @param string $uid POI的uid
     * @return array
     */
    public function placeEventDetail($uid)
    {
        return $this->invoke(self::API_PLACE_EVENTDETAIL, array('uid' => $uid));
    }


    public function invoke($api, array $params)
    {
        $params['output'] = self::OUTPUT;
        $params['ak'] = $this->apiKey;
        if ($this->_signRequired) {
            $params['sn'] = static::generateSign($this->securityKey, $api, $params);
            $params['timestamp'] = time();
        }
        $response = static::httpGet(self::BASE_URI . $api . '?' . http_build_query($params));
        if ($response === false) {
            return array('status' => -1, 'message' => 'Request Error.');
        }
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return array('status' => -2, 'message' => 'Invalid JSON Response.');
        }
        return $data;
    }


    protected static function generateSign($key, $path, array $params, $isPost = false)
    {
        if ($isPost) {
            ksort($params);
        }
        return md5(urlencode('/' . $path . '?' . http_build_query($params) . $key));
    }


    protected static function httpGet($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

}