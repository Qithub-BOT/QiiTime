#!/usr/bin/env php
<?php
/**
 * このスクリプトは呼び出されると時報をトゥートします
 * また、そのトゥート ID を `/data/tooted.json` に出力します.
 *
 * - Note: 呼び出された時間をトゥートするので、呼び出すタイミングに注意。
 */

 /* [Constants] ============================================================= */

define('TIME_NOW', time());

// ユーザー設定による定数のデフォルト値
const MSTDN_SCHEMA     = 'https';
const MSTDN_HOST       = 'qiitadon.com';
const MSTDN_VISIBILITY = 'direct';

// フラグ・その他定数
const SUCCESS = 0; // 成功時の終了ステータス
const FAILURE = 1; // 失敗時の終了ステータス
const DO_EXIT     = true;
const DO_NOT_EXIT = false;
const DO_NOT_ECHO = true; // print_r 時のフラグ
const PATH_DIR_DATA    = '/data';
const NAME_FILE_DATA   = 'tooted.json';
const NAME_FILE_LOG    = 'log.txt';
const COUNT_RETRY_MAX  = 2;  //トゥート失敗時のリトライ数
const SECS_SLEEP_RETRY = 1;  //リトライ時のインターバル秒
const LEN_ACCESSTOKEN  = 64; //アクセストークンの長さ

/* [PreProcess] ============================================================ */

// 作業ディレクトリをスクリプトに移動
if (false === chdir(dirname(__FILE__))) {
    print_error('* Fail to change working directory to the script directory.', DO_EXIT);
}

// デバッグモードの確認用表示
print_on_debug('- Working on DEBUG Mode');

// 必須の環境変数がセットされているか確認
if (! is_env_set()) {
    print_error('* Must environment variables missing.', DO_EXIT);
}

// 同一時間内の重複リクエスト防止
if (is_threshold_same_as_cache()) {
    $json_cached = '';
    if (is_mode_debug()) {
        $json_cached = PHP_EOL . '- Cached JSON:' . PHP_EOL . get_data_cached();
    }
    print_error('* Already tooted' . $json_cached, DO_EXIT);
}

/* [Main] ================================================================== */

/**
 * Build content to POST.
 * ----------------------
 * POST するトゥート内容を作成
 */

// テンプレートの文字列置き換えの一覧読み込み
require_once('list-replace.inc.php');

// メインのトゥート部取得
$toot_main = get_toot_main();
// CW （警告文）の内容取得
$toot_spoiler = get_toot_spoiler();

// POST するデータの作成
$data_post = [
    'status'     => $toot_main,
    'visibility' => get_visibility(),
];
// CW（警告文）がある場合セット。上記 $toot_main の内容は「もっと見る」に入ります。
if (! empty($toot_spoiler)) {
    $data_post['spoiler_text'] = $toot_spoiler;
}
$data_post = http_build_query($data_post, "", "&");

/**
 * Build request headers.
 * ----------------------
 * リクエストのヘッダー作成。
 */
$access_token     = get_accesstoken();
$hash_idempotency = hash('sha256', $toot_main . $toot_spoiler);
$name_useragent   = 'QiiTime-Dev';

$header = [
    'Content-Type: application/x-www-form-urlencoded',
    "Authorization: Bearer ${access_token}",
    "Idempotency-Key: ${hash_idempotency}",
    "User-Agent: {$name_useragent}",
];
$header = implode("\r\n", $header);

/**
 * トゥートの実行
 */
$count_retry  = 0;
$url_api_toot = get_url_toot();
$method  = 'POST';
$context = [
    'http' => [
        'method'  => $method,
        'header'  => $header,
        'content' => $data_post,
    ],
];

while (true) {
    if ($count_retry > COUNT_RETRY_MAX) {
        print_error('* Failed to request server. Max retry exceed.', DO_EXIT);
    }

    // トゥートの実行.
    // ヘッダに Idempotency-Key をセットしているので同一 Key の場合は何度トゥートしても
    // 成功した １つのトゥートのみが有効。トゥート済みの場合は 404 エラー。
    $result = file_get_contents($url_api_toot, false, stream_context_create($context));
    // トゥート成功時の処理
    if (false !== $result) {
        $array  = json_decode($result, JSON_OBJECT_AS_ARRAY);
        $id     = get_value('id', $array, null);
        if ((null !== $id) && ! empty($id) && ctype_digit($id)) {
            $result = $array;
            break;
        }
    }
    // トゥート失敗時の処理
    print_error("* Toot failed. Retry ${count_retry}/" . COUNT_RETRY_MAX);
    print_on_debug('- Server response:');
    print_on_debug($result);
    sleep(SECS_SLEEP_RETRY * $count_retry);
    $count_retry++;
}

/* Save Results */
if (! save_data($result)) {
    print_error('* Failed to save tooted data.');
}

print_stdout('- Toot success.');
print_stdout(file_get_contents('/data/tooted.json'));

exit(SUCCESS);

/* [Functions] ============================================================= */

function get_accesstoken()
{
    return get_envs('MSTDN_ACCESSTOKEN') ?: '';
}

function get_data_cached()
{
    static $json_cached;

    if (isset($json_cached)) {
        return $json_cached;
    }

    $path_file_data = get_path_file_data();
    if (! file_exists($path_file_data)) {
        return false;
    }
    $json_cached = file_get_contents($path_file_data);

    return $json_cached;
}

function get_envs($key=null)
{
    static $result;

    if (null !== $key) {
        return trim(getenv($key), '\'"');
    }

    if (isset($result)) {
        return $result;
    }

    $envs = getenv();
    foreach ($envs as $key => $value) {
        $result[$key] = trim($value, '\'"');
    }

    return $result;
}

function get_host()
{
    return get_envs('MSTDN_HOST') ?: MSTDN_HOST;
}

function get_icon_hour()
{
    $date_hour12 = (integer) date('h', TIME_NOW);
    $icon_hour   = strtr($date_hour12, [
        12 => '🕛', 11 => '🕚', 10 => '🕙', 9 => '🕘',
        8  => '🕗',  7 => '🕖',  6 => '🕕', 5 => '🕔',
        4  => '🕓',  3 => '🕒',  2 => '🕑', 1 => '🕐',
        0  => '🕛',
    ]);

    return $icon_hour;
}

function get_path_dir_data()
{
    $path_dir_data = realpath(PATH_DIR_DATA);

    if (empty($path_dir_data)) {
        print_error('* Directory path for Data not set.', DO_EXIT);
    }
    if (! is_dir($path_dir_data)) {
        print_error('* Data directory missing. Mount or create a directory at:' . $path_dir_data, DO_EXIT);
    }

    return $path_dir_data;
}

function get_path_file_data()
{
    $path_dir_data = get_path_dir_data();

    return $path_dir_data . DIRECTORY_SEPARATOR . NAME_FILE_DATA;
}

function get_path_file_log()
{
    $path_file_data = get_path_dir_data();

    return $path_file_data . DIRECTORY_SEPARATOR . NAME_FILE_LOG;
}

function get_schema()
{
    return get_envs('MSTDN_SCHEMA') ?: MSTDN_SCHEMA;
}

function get_threshold_from_time(int $timestamp)
{
    if (is_mode_debug()) {
        return date('YmdHi', $timestamp);
    }
    return date('YmdH', $timestamp);
}

function get_threshold_now()
{
    return get_threshold_from_time(TIME_NOW);
}

function get_toot_main()
{
    $template_main = get_envs('MSTDN_TOOT_MAIN') ?: file_get_contents('toot-main.tpl');
    $list_replace  = get_list_replace();
    return replace_str_in_template($template_main, $list_replace);
}

function get_toot_spoiler()
{
    $template_main = get_envs('MSTDN_TOOT_SPOILER') ?: file_get_contents('toot-main.tpl');
    if ('no_spoiler' === trim($template_main)) {
        return '';
    }
    $list_replace = get_list_replace();
    return replace_str_in_template($template_main, $list_replace);
}

function get_url_toot()
{
    $schema  = get_schema();
    $host    = get_host();
    $endpint = '/api/v1/statuses';

    return "${schema}://${host}${endpint}";
}

function get_value($key, $array, $default=null)
{
    return (isset($array[$key])) ? $array[$key] : $default;
}

function get_visibility()
{
    if (is_mode_debug()) {
        return 'direct';
    }

    return get_envs('MSTDN_VISIBILITY') ?: MSTDN_VISIBILITY;
}

function is_empty($key, $array)
{
    $value = get_value($key, $array, '');

    if (empty($value)) {
        return true;
    }
    return false;
}

function is_env_set()
{
    $envs   = get_envs();
    $result = true;

    if (is_empty('MSTDN_ACCESSTOKEN', $envs)) {
        print_error('* Env variable missing: MSTDN_ACCESSTOKEN');
        $result = false;
    }

    if (! is_valid_format_token(get_value('MSTDN_ACCESSTOKEN', $envs))) {
        print_error('* Invalid format in variable: MSTDN_ACCESSTOKEN');
        $result = false;
    }

//    if(is_empty('MSTDN_SCHEMA', $envs)){
//        print_error('* Env variable missing: MSTDN_SCHEMA');
//        $result = false;
//    }
//    if(is_empty('MSTDN_HOST', $envs)){
//        print_error('* Env variable missing: MSTDN_HOST');
//        $result = false;
//    }

    return $result;
}

function is_mode_debug()
{
    static $is_mode_debug;

    if (isset($is_mode_debug)) {
        return $is_mode_debug;
    }

    $value = get_envs('IS_MODE_DEBUG') ?: 'false';
    $is_mode_debug = ('false' !== strtolower($value));

    return $is_mode_debug;
}

function is_threshold_same_as_cache()
{
    print_on_debug('- Comparing threshold between current time and cache.');

    $cache_json  = get_data_cached();
    $cache_array = json_decode($cache_json, JSON_OBJECT_AS_ARRAY);

    $threshold_now   = (int) get_threshold_now();
    print_on_debug('  - Threshold current: '. $threshold_now);

    $threshold_cache = (int) get_value('threshold', $cache_array);
    print_on_debug('  - Threshold cached : '. $threshold_cache);

    return ($threshold_cache === $threshold_now);
}

function is_valid_format_token($string)
{
    return (ctype_alnum($string) && (LEN_ACCESSTOKEN === strlen($string)));
}

function log_to_file($string)
{
    $string        = trim($string) . PHP_EOL;
    $path_file_log = get_path_file_log();
    $timestamp     = date('Y-m-d H:i:s', time());
    $log           = "${timestamp} ${string}";

    return file_put_contents($path_file_log, $log, FILE_APPEND);
}

function print_on_debug($mix)
{
    if (! is_mode_debug()) {
        return;
    }

    if (is_string($mix)) {
        $result = $mix . PHP_EOL;
    } else {
        $result = print_r($mix, DO_NOT_ECHO);
    }

    log_to_file('DEBUG: ' . $result);
    echo $result;
}

function print_error($message, $exit_and_die=true)
{
    fputs(STDERR, trim($message) . PHP_EOL);
    if (is_mode_debug()) {
        log_to_file('ERROR: ' . $message);
    }
    if ($exit_and_die) {
        exit(FAILURE);
    }
}

function print_stdout($message)
{
    $message = trim($message) . PHP_EOL;
    if (is_mode_debug()) {
        log_to_file('LOG: ' . $message);
    }
    echo $message;
}

function replace_str_in_template($string, $list_to_replace)
{
    foreach ($list_to_replace as $from => $to) {
        $string = str_replace($from, $to, $string);
    }
    return $string;
}

function save_data(array $array)
{
    $result['threshold']    = get_threshold_now();
    $result['id']           = get_value('id', $array, '');
    $result['uri']          = get_value('uri', $array, '');
    $result['url']          = get_value('url', $array, '');
    $result['created_at']   = get_value('created_at', $array, '');
    $result['requested_at'] = date('Y-m-d\TH:i:s.Z\Z', TIME_NOW); //Without TimeZone

    if (empty($result['id'])) {
        print_on_debug('* Empty toot ID given while saving data. Given content:');
        print_on_debug($array);
        return false;
    }

    $path_file_data = get_path_file_data();
    $data_to_save   = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return file_put_contents($path_file_data, $data_to_save);
}
