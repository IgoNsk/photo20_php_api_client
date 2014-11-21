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
     * @var integer
     */
    public $code;

    /**
     * @param array $result
     * @param $code
     */
    public function __construct($result, $code)
    {
        $this->result = $result;
        $this->code = $code;
    }
}