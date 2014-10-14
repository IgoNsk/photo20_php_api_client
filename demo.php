<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');

	require __DIR__.'/Client.php';

	use \DG\API\Photo\Client;
	use \DG\API\Photo\PhotoCollection;
	use \DG\API\Photo\PhotoItem;
    use \DG\API\Photo\Exception as DGAPIPhotoException;

	$collection = new PhotoCollection();
	$collection
        ->add( new PhotoItem(100, '/tmp/1.php', [
            'description' => 'Photo 1 description',
        ]) )
        ->add( new PhotoItem(200, '/tmp/2.php', [
            'description' => 'Photo 2 description',
        ]) )
        ->add( new PhotoItem(300, '/tmp/1.php', [
            'description' => 'Photo 3 description',
        ]) )
    ;

    $client = new Client('my_cool_key');

    try
    {
        $res = $client->add($collection, $client::OBJECT_TYPE_BRANCH, 100500, $client::ALBUM_CODE_DEFAULT);
    } catch (DGAPIPhotoException $e)
    {
        $res = false;
        die( $e->getMessage() );
    }

    if($res)
    {
        $r = $client->upload($collection);
        var_dump($r);
    }
