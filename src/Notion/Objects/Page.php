<?php namespace Notion\Objects;

use Notion\ObjectBase;

class Page extends ObjectBase
{
    protected $context = 'update';

    protected $properties = [];

    protected $parent;

    public function setParent($type, $id): self
    {
        $this->parent[$type . '_id'] = $id;

        return $this;
    }

    public function prepareForRequest()
    {
        $data = [
            'parent' => $this->parent,
            'properties' => [],
        ];

        foreach ($this->properties as $property) {
            $value = $property->get();

            if (!$value) {
                continue;
            }

            $data['properties'][$property->name] = $value;
        }

        return $data;
    }

    public function initProperties($data): self
    {
        $this->properties = $data;

        return $this;
    }

    public function __get($property)
    {
        if (!isset($this->properties[$property])) {
            return $this->$property;
        }

        return $this->properties[$property]
            ->value();
    }

    public function __set($property, $value)
    {
        if (!isset($this->properties[$property])) {
            $this->$property = $value;
            return;
        }

        $this->properties[$property]->set($value);
    }

    public function __isset($property)
    {
        return isset($this->properties[$property]);
    }

    public function save()
    {
        ray($this->prepareForRequest());

        $options = [
            'body' => json_encode($this->prepareForRequest()),
        ];

        if ($this->context === 'create') {
            $response = $this->notion->getClient()->post('pages', $options);
            ray($response);
        }
    }

    public function setContext($context): self
    {
        $this->context = $context;

        return $this;
    }
}