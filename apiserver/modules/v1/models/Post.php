<?php

namespace apiserver\modules\v1\models;

use apiuser\modules\post\models\Post as FrontendPost;

class Post extends FrontendPost
{
    public function fields()
    {
        $fields = ['id', 'user_id', 'target', 'type', 'name', 'content', 'images', 'mainImage'];

        $fields['images'] = function($model) {
            return $model->listImages;
        };

        $fields['mainImage'] = function($model) {
            return $model->mainImage;
        };

        return $fields;
    }

    public function extraFields()
    {
        return [
          'ownerLastName' => function($model) {
            return $model->ownerLastName;
          },
            'ownerUsername' => $this->ownerUsername,
        ];
    }
}
