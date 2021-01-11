<?php


namespace HelloSebastian\ReactTableBundle\Response;


use HelloSebastian\ReactTableBundle\Data\DataBuilder;
use HelloSebastian\ReactTableBundle\Query\DoctrineQueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReactTableResponse
{
    private $requestData = array();

    /**
     * @var DoctrineQueryBuilder
     */
    private $doctrineQueryBuilder;

    /**
     * @var DataBuilder
     */
    private $dataBuilder;

    /**
     * @var string
     */
    private $callbackUrl;

    public function __construct(DoctrineQueryBuilder $doctrineQueryBuilder, DataBuilder $dataBuilder)
    {
        $this->doctrineQueryBuilder = $doctrineQueryBuilder;
        $this->dataBuilder = $dataBuilder;

        $resolver = new OptionsResolver();
        $this->configureRequestData($resolver);
        $this->requestData = $resolver->resolve(array());
    }

    public function handleRequest(Request $request)
    {
        $requestData = array();
        if ($request->isMethod("POST")) {
            $requestData = json_decode($request->getContent(), true);
        }

        if (is_null($this->callbackUrl)) {
            $this->callbackUrl = $request->getRequestUri();
        }

        $resolver = new OptionsResolver();
        $this->configureRequestData($resolver);
        $this->requestData = $resolver->resolve($requestData);
    }

    public function configureRequestData(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'isCallback' => false,
            'filtered' => array(),
            'sorted' => array(),
            'page' => 1,
            'pageSize' => 25,
        ));
    }

    public function getData()
    {
        $entities = $this->doctrineQueryBuilder->getSubsetData($this->requestData);

        return array(
            'data' => $this->dataBuilder->buildDataAsArray($entities),
            'totalCount' => $this->doctrineQueryBuilder->getTotalCount()
        );
    }

    public function isCallback()
    {
        return $this->requestData['isCallback'];
    }

    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }
}