<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');

	require __DIR__.'/Client.php';

	use \DG\API\Photo\Client;
	use \DG\API\Photo\Collection\LocalPhotoCollection;
	use \DG\API\Photo\Item\LocalPhotoItem;
    use \DG\API\Photo\Exception as DGAPIPhotoException;

	$collection = new LocalPhotoCollection();
	$collection
        ->add( new LocalPhotoItem(100, '/tmp/1.jpg', [
            'description' => 'Photo 1 description',
        ]) )
        ->add( new LocalPhotoItem(200, '/tmp/2.jpg', [
            'description' => 'Photo 2 description',
        ]) )
        ->add( new LocalPhotoItem(300, '/tmp/3.jpg', [
            'description' => 'Photo 3 description',
        ]) )
    ;

    $client = new Client('my_cool_key');

    $objectType = $client::OBJECT_TYPE_BRANCH;
    $objectId = 100500;
    $albumCode = $client::ALBUM_CODE_DEFAULT;

    try
    {
        $res = $client->add($collection, $objectType, $objectId, $albumCode);
    } catch (DGAPIPhotoException $e)
    {
        die( $e->getMessage() );
    }

    if($res)
    {
        try
        {
            $r = $client->upload($collection);
            var_dump($r);
        } catch (DGAPIPhotoException $e)
        {
            die( $e->getMessage() );
        }
    }

    try
    {
        $r = $client->get($objectId, $objectType, $albumCode);
        var_dump($r);
    } catch (DGAPIPhotoException $e)
    {
        die( $e->getMessage() );
    }

