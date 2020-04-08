<?php
/**
 * FSky7.ErrorPage
 * HTTP 错误页面
 * 
 * @author FlyingSky-CN
 * @license MIT License
 */

if (substr(php_sapi_name(), 0, 3) == 'cgi')
header('content-type: text/plain');

error_reporting(0);
define('DIR_build', __DIR__.'/build');

if (!is_dir(DIR_build)) {
    echo "warning: 找不到 build 文件夹.\n";
    if (!mkdir(DIR_build))
    exit("fatal: 无法创建 build 文件夹.\n");
}

if (!($errors = json_decode(file_get_contents('errors.json'), true)))
exit("fatal: 无法读取 errors.json .\n");

if (count($errors) < 1)
exit("warning: errors.json 内容为空, 没有渲染任务.\n");

if (!($config = json_decode(file_get_contents('config.json'), true)))
exit("fatal: 无法读取设置文件.\n");

$subtitle = isset($config['subtitle']) ? $config['subtitle'] : 'Web Server';
$favicon  = isset($config['favicon' ]) ? $config['favicon' ] : ''          ;
$server   = isset($config['server'  ]) ? $config['server'  ] : 'Web Server';
$darkmode_status = (isset($config['darkmode.status']) ? $config['darkmode.status'] : true) ? true : false;
$darkmode_domain = isset($config['darkmode.domain']) ? 'domain='.$config['darkmode.domain'] : '';
$addition = (isset($config['addition']) ? $config['addition'] : false) ? true : false;
$compress = (isset($config['compress']) ? $config['compress'] : false) ? true : false;

if ($compress): function compressHtml($html_source) {
	$chunks = preg_split('/(<!--<nocompress>-->.*?<!--<\/nocompress>-->|<nocompress>.*?<\/nocompress>|<pre.*?\/pre>|<textarea.*?\/textarea>|<script.*?\/script>)/msi', $html_source, -1, PREG_SPLIT_DELIM_CAPTURE);
	$compress = '';
	foreach ($chunks as $c) {
		if (strtolower(substr($c, 0, 19)) == '<!--<nocompress>-->') {
			$c = substr($c, 19, strlen($c) - 19 - 20);
			$compress .= $c;
			continue;
		} else if (strtolower(substr($c, 0, 12)) == '<nocompress>') {
			$c = substr($c, 12, strlen($c) - 12 - 13);
			$compress .= $c;
			continue;
		} else if (strtolower(substr($c, 0, 4)) == '<pre' || strtolower(substr($c, 0, 9)) == '<textarea') {
			$compress .= $c;
			continue;
		} else if (strtolower(substr($c, 0, 7)) == '<script' && strpos($c, '//') != false && (strpos($c, "\r") !== false || strpos($c, "\n") !== false)) {
			$tmps = preg_split('/(\r|\n)/ms', $c, -1, PREG_SPLIT_NO_EMPTY);
			$c = '';
			foreach ($tmps as $tmp) {
				if (strpos($tmp, '//') !== false) {
					if (substr(trim($tmp), 0, 2) == '//') {
						continue;
					}
					$chars = preg_split('//', $tmp, -1, PREG_SPLIT_NO_EMPTY);
					$is_quot = $is_apos = false;
					foreach ($chars as $key => $char) {
						if ($char == '"' && $chars[$key - 1] != '\\' && !$is_apos) {
							$is_quot = !$is_quot;
						} else if ($char == '\'' && $chars[$key - 1] != '\\' && !$is_quot) {
							$is_apos = !$is_apos;
						} else if ($char == '/' && $chars[$key + 1] == '/' && !$is_quot && !$is_apos) {
							$tmp = substr($tmp, 0, $key);
							break;
						}
					}
				}
				$c .= $tmp;
			}
		}
		$c = preg_replace('/[\\n\\r\\t]+/', ' ', $c);
		$c = preg_replace('/\\s{2,}/', ' ', $c);
		$c = preg_replace('/>\\s</', '> <', $c);
		$c = preg_replace('/\\/\\*.*?\\*\\//i', '', $c);
		$c = preg_replace('/<!--[^!]*-->/', '', $c);
		$compress .= $c;
	}
	return $compress;
} endif;

if ($addition) {
    if (!($addition_content = file_get_contents('addition.html')))
    exit("fatal: 无法读取 addition.html .\n");
} else {
    $addition_content = '';
}
if ($darkmode_status) {
    if (!($darkmode_script = file_get_contents('darkmode.js')))
    exit("fatal: 无法读取 darkmode.js .\n");
} else {
    $darkmode_script = '';
}

if (!($theme = file_get_contents('theme.html')))
exit("fatal: 无法读取 theme.html .\n");

echo "note: 开始渲染.\n";
$all = count($errors);

foreach ($errors as $num => $error) {
    $now = $num += 1;
    echo "note: 正在处理 $now/$all .\n";

    $code   = isset($error['code'  ]) ? $error['code'  ] : 100      ;
    $title  = isset($error['title' ]) ? $error['title' ] : 'title'  ;
    $icon   = isset($error['icon'  ]) ? $error['icon'  ] : 'close'  ;
    $text   = isset($error['text'  ]) ? $error['text'  ] : 'Text'   ;
    $reason = isset($error['reason']) ? $error['reason'] : 'Reason.';
    
    $output = str_replace(
        [
            '{{title}}',
            '{{subtitle}}',
            '{{favicon}}',
            '{{icon}}',
            '{{text}}',
            '{{reason}}',
            '{{server}}',
            '{{darkmode.domain}}',
            '{{darkmode}}',
            '{{addition}}'
        ],
        [
            $title,
            $subtitle,
            $favicon,
            $icon,
            $text,
            $reason,
            $server,
            $darkmode_domain,
            $darkmode_script,
            $addition_content
        ],
        $theme
    );

    if ($compress) $output = compressHtml($output);

    if (!file_put_contents("build/$code.html", $output))
    echo "error: 无法保存至 $code.html .\n";
}

echo "success: 任务全部完成.\n";