<?php

class SstwSearchItemModel implements JsonSerializable
{
    protected $id;

    protected $title = '';

    protected $originalTitle = '';

    protected $url = '';

    protected $aliases = [];

    protected $actions = [];

    protected $parentId = null;

    protected $menuFile = '';

    protected $menuSlug = '';

    protected $adminIsParent = false;

    protected $type = 'menu';

    protected $typeLabel = '';

    public function __construct($data = [])
    {
        if(! empty($data)) {
            $this->setData($data);
        }
    }

    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst($key);

            if(! method_exists($this, $setter)) {
                continue;
            }

            $this->{$setter}($value);
        }

        if(! $this->id) {
            $this->id = (string) wp_rand(1000, 10000);
        }

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getOriginalTitle()
    {
        return $this->originalTitle;
    }

    /**
     * @param string $originalTitle
     */
    public function setOriginalTitle($originalTitle)
    {
        $this->originalTitle = $originalTitle;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param array $aliases
     */
    public function setAliases($aliases)
    {
        if(!$aliases) {
            $aliases = [];
        }

        $this->aliases = (array) $aliases;
    }

    /**
     * @return null|string
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param string $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getMenuFile()
    {
        return $this->menuFile;
    }

    /**
     * @param string $menuFile
     */
    public function setMenuFile($menuFile)
    {
        $this->menuFile = $menuFile;
    }

    /**
     * @return string
     */
    public function getMenuSlug()
    {
        return $this->menuSlug;
    }

    /**
     * @param string $menuSlug
     */
    public function setMenuSlug($menuSlug)
    {
        $this->menuSlug = $menuSlug;
    }

    /**
     * @return bool
     */
    public function getAdminIsParent()
    {
        return $this->adminIsParent;
    }

    /**
     * @param bool $adminIsParent
     */
    public function setAdminIsParent($adminIsParent)
    {
        $this->adminIsParent = $adminIsParent;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return $this->type_label ?: __('Admin Menu', 'sstw');
    }

    /**
     * @param string $type_label
     */
    public function setTypeLabel(string $type_label)
    {
        $this->type_label = $type_label;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;
    }

    public static function create($data)
    {
        return new static($data);
    }

    protected function firstCharacterFromEachWord($name)
    {
        preg_match_all('/(\b\w)/im', $name, $output_array);

        if (! isset($output_array[0]) || empty($output_array[0])) {
            return '';
        }

        return strtoupper(implode('', $output_array[0]));
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'short' => $this->firstCharacterFromEachWord($this->title),
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'aliases' => $this->getAliases(),
            'actions' => $this->getActions(),
            'parentId' => $this->getParentId(),
            'type' => $this->getType(),
            'typeLabel' => $this->getTypeLabel()
        ];
    }
}