<?php
require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
   die();
}
header('Content-Type: text/plain; charset=utf-8');
$projectID=-1;

try {
    
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
$file = fopen($_FILES['file']['tmp_name'],"r");
    echo(fgets($file));

    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here. 
    if ($_FILES['file']['size'] > 100000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['file']['tmp_name']),
        array(
            'txt' => 'text/plain',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
        sprintf('server/php/files/'.$projectID.'/%s%s',
            sha1_file($_FILES['file']['tmp_name']),
            '.txt'
        )
   )) 
{
        throw new RuntimeException('Failed to move uploaded file.');
    }

    echo 'File is uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

?>
