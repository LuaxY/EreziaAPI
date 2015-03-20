<?php

class ShopController extends \BaseController {

    public function shop()
    {
        $req = $this->input();

        if ($req->method == "Home")
        {
            if ($req->params[0] == "DOFUS_INGAME")
            {
                $result = new stdClass;
        		$result->content = $this->content();
                return $this->result($result);

                //return file_get_contents("shop.json");
            }
            else
                return $this->softError("KEYUNKNOWN");
        }

        return $this->criticalError("Method not found");
    }

    private function content()
    {
        $content = new stdClass;

        $content->categories = $this->categories();
        $content->gondolahead_main = $this->gondolahead_main();
        $content->gondolahead_article = $this->gondolahead_article();
        $content->hightlight_carousel = $this->hightlight_carousel();
        $content->hightlight_image = $this->hightlight_image();

        return $content;
    }

    private function categories()
    {
        $data = new stdClass;

        $data->result = true;
        $data->categories = array();
        $data->categories[] = $this->categories_categorie(1, "EREZIA_SHOP", "Boutique Erezia");

        return $data;
    }

    private function categories_categorie($id, $key, $name)
    {
        $categorie = new stdClass;

        $categorie->id = $id;
        $categorie->key = $key;
        $categorie->name = $name;
        $categorie->displaymode = "MOSAIC";
        $categorie->description = "";
        $categorie->image = false;
        $categorie->child = array();
        $categorie->child[] =  $this->categories_categorie_child(1, "Familiers");
        $categorie->child[] =  $this->categories_categorie_child(2, "Montures");
        $categorie->child[] =  $this->categories_categorie_child(3, "Objets Vivants");
        $categorie->child[] =  $this->categories_categorie_child(4, "Services");

        return $categorie;
    }

    private function categories_categorie_child($id, $name)
    {
        $child = new stdClass;

        $child->id = $id;
        $child->key = null;
        $child->name = $name;
        $child->displaymode = "MOSAIC";
        $child->description = "";
        $child->image = false;

        return $child;
    }

    private function gondolahead_main()
    {
        $data = new stdClass;

        $data->result = true;
        $data->gondolaheads = array();

        return $data;
    }

    private function gondolahead_article()
    {
        $data = new stdClass;

        $data->result = true;
        $data->count = 0;
        $data->articles = array();

        return $data;
    }

    private function hightlight_carousel()
    {
        $data = new stdClass;

        $data->result = true;
        $data->hightlights = array();

        return $data;
    }

    private function hightlight_image()
    {
        $data = new stdClass;

        $data->result = true;
        $data->hightlights = array();

        return $data;
    }

}
