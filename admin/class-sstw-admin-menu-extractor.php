<?php

class SstwAdminMenuExtractor
{

    protected $items = [];

    public static function items()
    {
        return (new static)->extract();
    }

    protected function getId($id = null)
    {
        if(! $id) {
            $id = (string) wp_rand(500, 50000);
        }

        $id = preg_replace('|[^a-zA-Z0-9]|', '-', $id);

        return 'sstw_' . esc_attr(sanitize_title($id));
    }

    public function extract()
    {
        global $menu, $submenu;

        $menuItems = $menu;
        $subMenuItems = $submenu;

        $menuItems = (array) apply_filters('sstw_admin_menu_items', $menuItems);
        $subMenuItems = (array) apply_filters('sstw_admin_sub_menu_items', $subMenuItems);

        foreach ($menuItems as $key => $parent) {
            $subItems = [];

            if (! empty($subMenuItems[$parent[2]])) {
                $subItems = $subMenuItems[$parent[2]];
            }

            $parent = $this->extractParent($parent, $subItems);

            if(! $parent) {
                continue;
            }

            $this->items[] = $parent;

            foreach ($subItems as $subKey => $child) {
                $child = $this->extractChild($child, $parent);

                if(! $child) {
                    continue;
                }

                $this->items[] = $child;
            }

        }

        return (array) apply_filters('sstw_static_items', $this->items);
    }

    protected function extractParent($item, $subItems = [])
    {
        global $submenu;

        $class = empty($item[4]) ? esc_attr($item[4]) : '';
        $id = $this->getId($item[5]);
        $title = sanitize_title(trim(wp_strip_all_tags(wptexturize($item[0]), true)));
        $url = null;
        $aliases = [];
        $menuSlug = $item[2];
        $adminIsParent = false;

        if (! empty($title)) {
            $aliases = [$this->firstCharacterFromEachWord($title)];
        }

        if (strpos($class, 'wp-menu-separator') !== false) {
            return null;
        }

        if (! empty($subItems)) {
            $subItems = array_values($subItems);
            $menu_hook = get_plugin_page_hook($subItems[0][2], $item[2]);
            $menu_file = $subItems[0][2];

            if (false !== ($pos = strpos($menu_file, '?'))) {
                $menu_file = substr($menu_file, 0, $pos);
            }

            if (! empty($menu_hook) || (('index.php' != $subItems[0][2]) && file_exists(WP_PLUGIN_DIR . "/$menu_file") && ! file_exists(ABSPATH . "/wp-admin/$menu_file"))) {
                $adminIsParent = true;
                $url = "admin.php?page={$subItems[0][2]}";
            } else {
                $url = $subItems[0][2];
            }
        }

        elseif (! empty($item[2]) && current_user_can($item[1])) {
            $menu_hook = get_plugin_page_hook($item[2], 'admin.php');
            $menu_file = $item[2];

            if (false !== ($pos = strpos($menu_file, '?'))) {
                $menu_file = substr($menu_file, 0, $pos);
            }

            if (! empty($menu_hook) || (('index.php' != $item[2]) && file_exists(WP_PLUGIN_DIR . "/$menu_file") && ! file_exists(ABSPATH . "/wp-admin/$menu_file"))) {
                $adminIsParent = true;
                $url = "admin.php?page={$item[2]}";
            } else {
                $url = $item[2];
            }
        }

        if (empty($subItems) && empty($title)) {
            return null;
        }

        if (empty($subItems) && ! $url) {
            return null;
        }

        return SstwSearchItemModel::create(
            compact(
                'title',
                'url',
                'aliases',
                'id',
                'menuFile',
                'menuSlug',
                'adminIsParent'
            )
        );
    }

    protected function extractChild($subItem, SstwSearchItemModel $parent = null)
    {
        if (! current_user_can($subItem[1])) {
            return null;
        }

        $menuFile = $parent->getMenuFile();

        if (false !== ($pos = strpos($menuFile, '?'))) {
            $menuFile = substr($menuFile, 0, $pos);
        }

        $menu_hook = get_plugin_page_hook($subItem[2], $parent->getMenuSlug());
        $subFile = $subItem[2];

        if (($pos = strpos($subFile, '?')) !== false) {
            $subFile = substr($subFile, 0, $pos);
        }

        $originalTitle = trim(wp_strip_all_tags(wptexturize($subItem[0]), true));

        if (! empty($menu_hook) || (('index.php' != $subItem[2]) && file_exists(WP_PLUGIN_DIR . "/$subFile") && ! file_exists(ABSPATH . "/wp-admin/$subFile"))) {
            if ((! $parent->getAdminIsParent() && file_exists(WP_PLUGIN_DIR . "/$menuFile") && ! is_dir(WP_PLUGIN_DIR . "/{$parent->getMenuSlug()}")) || file_exists($menuFile)) {
                $url = add_query_arg(['page' => $subItem[2]], $parent->getMenuSlug());
            } else {
                $url = add_query_arg(['page' => $parent->getMenuSlug()], 'admin.php');
            }

            $url = esc_url($url);
        } else {
            $url = $subItem[2];
        }

        $title = $parent->getTitle() . ' > ' . $originalTitle;
        $aliases = [
            $originalTitle,
            $this->firstCharacterFromEachWord($title)
        ];

        $parentId = $parent->getId();
        $id = $this->getId($url);
        $id = $parentId . '-' . $id;

        return SstwSearchItemModel::create(
            compact(
                'title',
                'url',
                'aliases',
                'id',
                'parentId'
            )
        );
    }

    protected function firstCharacterFromEachWord($name)
    {
        preg_match_all('/(\b\w)/im', $name, $output_array);

        if (! isset($output_array[0]) || empty($output_array[0])) {
            return '';
        }

        return strtoupper(implode('', $output_array[0]));
    }

}