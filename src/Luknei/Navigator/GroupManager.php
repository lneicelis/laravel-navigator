<?php

namespace Luknei\Navigator;

use Illuminate\Foundation\Application;

/**
 * Class GroupManager
 * @package Luknei\Navigator
 */
class GroupManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var array
     */
    protected $template = [
        'default' => ['', '<ul>', '<li>', '</li>', '</ul>']
    ];
    /**
     * @var int
     */
    protected $maxDepth = -1;

    /**
     * @var
     */
    protected $name;

    protected $customKeys = ['odd','even', 'default'];

    /**
     * @param string $key
     * @param array $variables
     */
    public function add($key, array $variables)
    {
        if (is_string($key) && is_array($variables)) {
            array_set($this->map, $key , []);
            array_set($this->map, $key.'.__NAME' , $key);
            $this->variables[$key] = $variables;
        }
    }

    /**
     * @param integer $depth
     */
    public function setMaxDepth($depth)
    {
        if (is_int($depth)) {
            $this->maxDepth = $depth;
        }
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    protected function traverse()
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($this->map),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * @return int
     */
    public function getMaxDepth()
    {
        if ($this->maxDepth !== -1) {
            return $this->maxDepth;
        }

        /** @var \RecursiveIteratorIterator $iterator */
        $iterator = $this->traverse();

        foreach ($iterator as $key => $variables) {
            if ($iterator->getDepth() > $this->maxDepth) {
                $this->maxDepth = $iterator->getDepth();
            }
        }

        return $this->maxDepth;
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    public function getIterator()
    {
        return $this->traverse();
    }

    /**
     * @param $template
     * @return string
     */
    public function render($template)
    {
        $this->extractTemplates($template);

        return $this->make($this->map);
    }

    /**
     * @param $fullTemplate
     */
    public function extractTemplates($fullTemplate)
    {
        $depths = range(1, $this->getMaxDepth());
        $keys = array_merge($depths, $this->customKeys);
        foreach ($keys as $key) {
            $pattern = sprintf('/@depth\(%s\)(.*)@foreach(.*)@subgroup(.*)@endforeach(.*)@stop/siU', $key);
            if (preg_match($pattern, $fullTemplate, $match)) {
                $this->template[$key] = $match;
            }
        }
    }

    /**
     * TODO: split this action to smaller ones
     *
     * @param array $group
     * @param int $depth
     * @return string
     */
    protected function make(array $group, $depth = 1)
    {
        /* Returning empty string if array does not have any children*/
        if(count($group) === 1 && isset($group['__NAME'])) return '';

        /* Getting the right template for current depth */
        $templateParts = $this->getTemplateParts($depth);

        /* Building partial */
        $template[] = $templateParts[1]; //opener
        foreach ($group as $key => $val) {
            if ($key == '__NAME') continue;
            $name = $val['__NAME'];
            $this->name[$depth] = $key;
            $template[] = $this->parse($name, $templateParts[2]); //innder opener

            if (count($group) > 1 && $depth <= $this->maxDepth){
                $template[] = $this->make($val, $depth+1);
            }

            $template[] = $this->parse($name, $templateParts[3]); //inner closer
        }
        $template[] = $templateParts[4]; //closer

        return implode($template);
    }

    protected function getTemplateParts($depth)
    {
        $templates = $this->template;
        if (isset($templates[$depth])) {
            return $templates[$depth];
        }

        if ($depth%2 == 0) {
            if (isset($templates['even'])) {
                return $templates['even'];
            }
        } else {
            if (isset($templates['odd'])) {
                return $templates['odd'];
            }
        }

        return $templates['default'];

    }


    /**
     * @param $key
     * @param $template
     * @return mixed
     */
    protected function parse($key, $template)
    {
        $variables = $this->variables[$key];
        foreach ($variables as $key => $var) {
            $pattern = '/{{\s?\$'.$key.'\s?}}/s';
            $template = preg_replace($pattern, $var, $template);
        }

        return $template;
    }

} 