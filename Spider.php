<?php
/**
 * 简易网络爬虫
*/

define('STORARGE_PATH', dirname(__FILE__) . '/urls/');
if ( ! file_exists(STORARGE_PATH) ) {
    mkdir(STORARGE_PATH);
}

$todo_list = array();
$visited_list = array();



# 添加入口地址
$todo_list[] = 'http://news.baidu.com/';

# 抓取主逻辑，从todo_list中获取下一个要抓取的地址
while ( ($url = array_shift($todo_list)) != null ) {
    echo "+-Try to get content from {$url} ... ";
    # 获取url的html内容
    $html = get_html($url, true);
    if ( $html == null ) {
        # 失败的网址丢弃
        echo " --[Failed]\n";
        continue;
    }

    echo " --[Ok]\n";

    # 提取链接
    echo "+-Try to get links ... ";
    $links = get_links($html);
    $lnk_r = count($links);
    echo " --[{$lnk_r}]\n";
    # echo json_encode($links), "\n";
    # exit();

    # 存储抓取的内容
    echo "+-Try to save the content ... ";
    echo save($url, $html, $links) ? " --[Ok]\n" : " --[Failed]";

    # 把提取的链接存储到todo_list中去
    # 前提是这个链接不存在在visited_list中(没被抓取过)
    foreach ( $links as $link ) {
        $md5_link = md5($link['url']);
        if ( ! isset($visited_list[$md5_link]) ) {
            $todo_list[] = $link['url'];
        }
    }

    # 把当前的url标记为已经抓取
    $visited_list[md5($url)] = $url;
    echo "|--Finished for {$url}\n";
}

echo "+-Done\n";



/**
 * 从给定的html内容中提取链接返回
 *
 * @param   $html
 * @return  Array
*/
function get_links($html)
{
    $links = array();
    $pattern = '/<a[^>]*href="(.*?)"[^>]*>(.*?)<\/a>/i';
    if ( preg_match_all($pattern, $html, $m, PREG_SET_ORDER) != false ) {
        # var_dump($m);
        foreach ( $m as $mi ) {
            $url = trim($mi[1]);
            if ( strlen($url) < 1 
                || strpos($url, '#') === 0
                || strpos($url, 'javascript:') === 0 
                || strpos($url, 'http') !== 0 ) {
                continue;
            }

            $til = trim($mi[2]);
            if ( strlen($til) < 1 ) {
                continue;
            }

            $links[] = array(
                'url' => $url,
                'til' => $til
            );
        }
    }

    return $links;
}


/**
 * 净化指定的html代码
 * 1, 过滤掉js代码
 * 2, 过滤掉css代码
 *
 * @param   $html
 * @return  String
*/
function sanitize_html($html)
{
    $pattern = array(
        '/<script[^>]*>[\s\S]*?<\/script>/i',
        '/<style[^>]*>[\s\S]*?<\/style>/i'
    );

    foreach ( $pattern as $p ) {
        $html = preg_replace($p, ' ', $html);
    }

    return $html;
}


/**
 * 把抓的内容存储
 *
 * @param   $url
 * @param   $html
 * @param   $links
 * @return  Boolean
*/
function save($url, $html, $links)
{
    $filename = md5($url) . '.json';
    $content  = array(
        'url' => $url,
        'content' => $html,
        'links' => $links
    );

    $cjson = json_encode($content);
    return file_put_contents(STORARGE_PATH . $filename, $cjson) == strlen($cjson);
}


/**
 * 获取指定网址的html内容
 *
 * @param   $url
 * @param   $filter 是否过滤html代码
 * @return  Mixed String for succeed or null for failed
*/
function get_html($url, $filter=false)
{
    # return file_get_contents($url);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36'
    ));

    $ret  = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    if( intval($info["http_code"]) == 200 ) {
        return $filter ? sanitize_html($ret) : $ret;
    }

    return null;
}
?>
