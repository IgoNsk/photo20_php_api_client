<?php
namespace DG\API\Photo\Transport;

/**
 * Class TransportResult
 * Структура, предназначеная для сохранения результат выполнения запроса к удаленному серверу
 * @package DG\API\Photo\Transport
 */
class TransportResult
{
    /**
     * @var string
     */
    public $result;

    /**
     * @var array
     */
    private $params;

    /**
     * @param $result
     * @param array $params
     */
    public function __construct($result, array $params = [])
    {
        $this->result = $result;
        $this->setParams($params);
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }
}