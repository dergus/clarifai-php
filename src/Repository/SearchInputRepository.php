<?php
/**
 * Clarifai SearchInput
 *
 * @category Input
 * @package  Clarifai
 * @author   Darryn Ten <darrynten@github.com>
 * @license  MIT <https://github.com/darrynten/clarifai-php/blob/master/LICENSE>
 * @link     https://github.com/darrynten/clarifai-php
 */

namespace DarrynTen\Clarifai\Repository;

use DarrynTen\Clarifai\Entity\Concept;
use DarrynTen\Clarifai\Entity\Input;

/**
 * Single Clarifai Input
 *
 * @package Clarifai
 */
class SearchInputRepository extends InputRepository
{
    /**
     * parameter for input Concepts search
     */
    const INPUT_CONCEPTS = 'input_concepts';

    /**
     * parameter for output Concepts search
     */
    const OUTPUT_CONCEPTS = 'output_concepts';

    /**
     * parameter for input Metadata search
     */
    const METADATA = 'metadata';

    /**
     * parameter for input image search
     */
    const IMAGES = 'images';

    /**
     * parameter for reverse image search
     */
    const REVERSED_IMAGES = 'reversed_images';

    /**
     * Searches Inputs
     *
     * @param array $params
     *
     * @return Input[] array
     */
    public function search($params, array $options = [])
    {
        $searchResult = $this->getRequest()->request(
            'POST',
            'searches',
            $this->getSearchQuery($params) + $options
        );

        return $this->getInputsFromSearchResult($searchResult);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getSearchQuery($params)
    {
        $data['query'] = [];
        $data['query']['ands'] = [];

        if (isset($params[self::INPUT_CONCEPTS])) {
            $data['query']['ands'] = $this->getInputConceptsQuery(
                $data['query']['ands'],
                $params[self::INPUT_CONCEPTS]
            );
        }

        if (isset($params[self::OUTPUT_CONCEPTS])) {
            $data['query']['ands'] = $this->getOutputConceptsQuery(
                $data['query']['ands'],
                $params[self::OUTPUT_CONCEPTS]
            );
        }

        if (isset($params[self::METADATA])) {
            $data['query']['ands'] = $this->getMetadataQuery($data['query']['ands'], $params[self::METADATA]);
        }

        if (isset($params[self::IMAGES])) {
            $data['query']['ands'] = $this->getImagesQuery($data['query']['ands'], $params[self::IMAGES]);
        }

        if (isset($params[self::REVERSED_IMAGES])) {
            $data['query']['ands'] = $this->getReverseImagesQuery(
                $data['query']['ands'],
                $params[self::REVERSED_IMAGES]
            );
        }

        return $data;
    }

    /**
     * Returns data Query Part
     *
     * @param array $data
     * @param string $type
     *
     * @return array $data
     */
    public function setData($data, $type)
    {
        return [$type => ['data' => $data]];
    }

    /**
     * Generates Input Concept search query and adds it to existing data
     *
     * @param $data
     * @param Concept[] $concepts
     *
     * @return array $data
     */
    public function getInputConceptsQuery($data, $concepts)
    {
        foreach ($concepts as $concept) {
            $data[] = $this->setData(
                [
                    'concepts' => [
                        [
                            'name' => $concept->getName(),
                            'value' => $concept->getValue(),
                        ],
                    ],
                ],
                'input'
            );
        }

        return $data;
    }

    /**
     * Generates Output Concept search query and adds it to existing data
     *
     * @param $data
     * @param Concept[] $concepts
     *
     * @return array $data
     */
    public function getOutputConceptsQuery($data, $concepts)
    {
        foreach ($concepts as $concept) {
            $data[] = $this->setData(
                [
                    'concepts' => [
                        [
                            'name' => $concept->getName(),
                            'value' => $concept->getValue(),
                        ],
                    ],
                ],
                'output'
            );
        }

        return $data;
    }

    /**
     * Generates Metadata search query and adds it to existing data
     *
     * @param $data
     * @param array $metadata
     *
     * @return array $data
     */
    public function getMetadataQuery($data, $metadata)
    {
        foreach ($metadata as $searchMetadata) {
            $data[] = $this->setData(['metadata' => $searchMetadata], 'input');
        }

        return $data;
    }

    /**
     * Generates Image search query and adds it to existing data
     *
     * @param $data
     * @param Input[] $inputs
     *
     * @return array $data
     */
    public function getImagesQuery($data, $inputs)
    {
        foreach ($inputs as $input) {
            $data[] = $this->setData(['image' => ['url' => $input->getImage()]], 'input');
        }

        return $data;
    }

    /**
     * Generates Reverse Image search query and adds it to existing data
     *
     * @param $data
     * @param Input[] $inputs
     *
     * @return array $data
     */
    public function getReverseImagesQuery($data, $inputs)
    {
        foreach ($inputs as $input) {
            $data[] = ['output' => $this->setData(['image' => ['url' => $input->getImage()]], 'input')];
        }

        return $data;
    }

    /**
     * Searches Inputs by predicted Concepts
     *
     * @param Concept[] $concepts
     *
     * @return Input[] array
     */
    public function searchByPredictedConcepts($concepts)
    {
        $searchResult = $this->getRequest()->request(
            'POST',
            'searches',
            $this->getSearchQuery([self::OUTPUT_CONCEPTS => $concepts])
        );

        return $this->getInputsFromSearchResult($searchResult);
    }

    /**
     * Searches Inputs by predicted Concepts
     *
     * @param Concept[] $concepts
     *
     * @return Input[] array
     */
    public function searchByUserSuppliedConcepts($concepts)
    {
        $searchResult = $this->getRequest()->request(
            'POST',
            'searches',
            $this->getSearchQuery([self::INPUT_CONCEPTS => $concepts])
        );

        return $this->getInputsFromSearchResult($searchResult);
    }

    /**
     * Searches Inputs custom Metadata
     *
     * @param array $metadata
     *
     * @return Input[] array
     */
    public function searchByCustomMetadata($metadata)
    {
        $searchResult = $this->getRequest()->request(
            'POST',
            'searches',
            $this->getSearchQuery([self::METADATA => $metadata])
        );

        return $this->getInputsFromSearchResult($searchResult);
    }

    /**
     * Searches Inputs custom Metadata
     *
     * @param Input[] $inputs
     *
     * @return Input[] array
     */
    public function searchByReverseImage($inputs)
    {
        $searchResult = $this->getRequest()->request(
            'POST',
            'searches',
            $this->getSearchQuery([self::REVERSED_IMAGES => $inputs])
        );

        return $this->getInputsFromSearchResult($searchResult);
    }

    /**
     * Searches Inputs custom Metadata
     *
     * @param Input[] $inputs
     *
     * @return Input[] array
     */
    public function searchByMatchUrl($inputs)
    {
        $searchResult = $this->getRequest()->request(
            'POST',
            'searches',
            $this->getSearchQuery([self::IMAGES => $inputs])
        );

        return $this->getInputsFromSearchResult($searchResult);
    }

    /**
     * Parses Search Request Result and gets Inputs
     *
     * @param $searchResult
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getInputsFromSearchResult($searchResult)
    {
        $input_array = [];

        if (!isset($searchResult['hits'])) {
            throw new \Exception('Hits Not Found');
        }

        foreach ($searchResult['hits'] as $hit) {
            if (!isset($hit['input'])) {
                throw new \Exception('Inputs Not Found');
            }
            $input = new Input($hit['input']);
            $input_array[] = $input;
        }

        return $input_array;
    }
}
