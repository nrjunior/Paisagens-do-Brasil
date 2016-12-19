<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;

final class LandscapesOfBrazil
{
    private $fileXML;

    public  function __invoke(Request $request, Response $response, $args)
    {
        $this->setFileXML(__DIR__ . '/../../../data/paisagens_do_brasil.xml');

        if(file_exists($this->getFileXML()))
        {
            $amount_region = isset($args['amount_region']) ? $args['amount_region'] : 1;
            $amount_local = isset($args['amount_local']) ? $args["amount_local"] : 5;
            $forceFilecCached = isset($request->getQueryParams()['forceFileCached']) ? $request->getQueryParams()['forceFileCached'] : false;

            FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
            $key = FileSystemCache::generateCacheKey('cache', null);
            $newXML = FileSystemCache::retrieve($key);

            if ($newXML === false || $forceFilecCached == true)
            {
                $reader = json_decode(json_encode(simplexml_load_file($this->getFileXML())), true);
                $reader = $reader['region'];
                $newXML = array();


                if(count($reader) < $amount_region)
                {
                    $amount_region = count($reader);
                }

                for($i=0; $i<$amount_region;$i++)
                {
                    $indice_region = rand(0, count($reader) -1);
                    $newXML[$i] = array(
                        'name' => $reader[$indice_region]['name'],
                        'local'=> array()
                    );

                    if (count($reader[$indice_region]['local']['item']) < $amount_local)
                    {
                        $amount_local = count($reader[$indice_region]['local']['item']);
                    }

                    for ($l = 0; $l < $amount_local; $l++)
                    {
                        $indice_local = rand(0, count($reader[$indice_region]['local']['item']) -1);
                        $newXML[$i]['local'][] = $reader[$indice_region]['local']['item'][$indice_local];

                        unset($reader[$indice_region]['local']['item'][$indice_local]);
                        shuffle($reader[$indice_region]['local']['item']);
                    }

                    unset($reader[$indice_region]);
                    shuffle($reader);

//                var_dump($reader[$indice_region]['local']['item']);
//                die;
                }

                FileSystemCache::store($key, $newXML, 432000);

            }
        }

        else
        {
            $newXML = array(
                'status' => 'ERROR',
                'message' => 'Arquivo não encontrado'
            );
        }

        $xmlMaker = new XMLBuilder('root');
        $xmlMaker->load($newXML);
        $xml_output = $xmlMaker->createXML(true);
        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text-html');

        if(isset($newXML['status']))
        {
            if($newXML['status'] == 'ERROR')
            {
                $response = $response->withStatus(404);
            }
        }

        return $response;

        //print_r($newXML);

    }


    public function getFileXML()
    {
        return $this->fileXML;
    }

    public function setFileXML($fileXML)
    {
        $this->fileXML = $fileXML;
    }

}