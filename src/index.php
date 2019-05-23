<?php
/**
 * QiiTime のフロントエンドです。最後の時報トゥートの ID を JSON 形式で返します.
 */

/* [Settings] ============================================================== */

$path_dir_data  = '/data';
$name_file_json = 'tooted.json';
$path_file_json = "{$path_dir_data}/{$name_file_json}";

/* [Main] ================================================================== */

if (! file_exists($path_file_json)) {
    print_error_in_json('JSON file missing. No tooted data found.');
    exit(1);
}

print_header();
echo trim(file_get_contents($path_file_json)) . PHP_EOL;
exit(0);

/* [Functions] ============================================================= */

function get_Etag()
{
    return hash('md5', time());
}

function is_CLI()
{
    /** * @SuppressWarnings("PHPMD.SuperGlobals") */
    return PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR']);
}

function print_array_in_json($array)
{
    $json = json_encode($array, JSON_PRETTY_PRINT);

    print_header();
    echo $json . PHP_EOL;
}

function print_error_in_json($message)
{
    $array = [
        'status' => 'error',
        'message' => $message,
    ];

    print_array_in_json($array);
}

function print_header()
{
    if (! is_CLI()) {
        $etag = get_Etag();
        header('content-type: application/json; charset=utf-8');
        header("ETag: \"{$etag}\"");
    }
}
