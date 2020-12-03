<?php
namespace apiadmin\modules\adminxx\controllers;

use apiadmin\controllers\MainController;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\AccessControl;
use apiadmin\modules\adminxx\models\AuthItemX;
use apiadmin\modules\adminxx\models\filters\AuthItemFilter;
use yii\rbac\Item;
use yii\helpers\Url;

/**
 * Class AuthItemController
 * Управление разрешениями и ролями
 * @package app\modules\adminxx\controllers
 */
class AuthItemController extends MainController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index',
                    ],
                    'roles'      => ['adminAuthItemList' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'create', 'update',  'delete', 'assign', 'revoke'
                    ],
                    'roles'      => ['adminAuthItemCRUD', ],
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * +++ Список ролей, разрешений index
     * @return string
     */
    public function actionIndex()
    {
        $q=1;
        $dataProvider = new ActiveDataProviderConserve([
            'filterModelClass' => AuthItemFilter::class,
            'conserveName' => 'authItemAdminGrid',
            'pageSize' => 15,
        ]);
        return $this->render('index',[
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * +++ Создание роли/разрешения create
     * @param $type
     * @return string|\yii\web\Response
     */
    public function actionCreate($type)
    {
        $model = new AuthItemX();
        $model->type = $type;
        if ($model->load(\Yii::$app->getRequest()->post())) {
            if ($model->save()) {
                return $this->redirect([Url::toRoute('/adminxx/auth-item/update'), 'name' => $model->name]);
            }
        }
        return $this->render('create',
            [
                'model' => $model,
            ]);
    }

    /**
     * +++ Редактирование роли/разрешения update
     * @param $name
     * @return string|\yii\web\Response
     */
    public function actionUpdate($name )
    {
        $tmp = 1;
        $model =  AuthItemX::find()
            ->where(['name' => $name])
            ->one();
        if (isset($model)){
            $assigments = AuthItemX::getItemsXle($model->type, $name);
            if ($model->load(\Yii::$app->getRequest()->post())) {
                if (\Yii::$app->getRequest()->post('delete-button')){
                    $manager = \Yii::$app->authManager;
                    $item = ($model->type == AuthItemX::TYPE_ROLE) ?
                        $manager->getRole($model->name) :
                        $manager->getPermission($model->name);
                    $manager->remove($item);
                    return $this->redirect(Url::toRoute('/adminxx/auth-item'));
                }
                if ($model->save()) {
                    return $this->redirect(Url::toRoute('/adminxx/auth-item'));
                }
            }
            return $this->render('update', [
                'model' => $model,
                'assigments' => $assigments,
                ]);

        } else {
            return $this->redirect(Url::toRoute('/adminxx/auth-item'));
        }
    }

    /**
     * +++ Удаление роли/разрешения delete
     */
    public function actionDelete()
    {

    }

    /**
     * +++ Назначение итему ролей, разрешений, роутов
     * @param string $id
     * @param string $type (roles, permissions, routs)
     * @param array $items
     * @return string
     */
    public function actionAssign()
    {
        try {
            $name    = \Yii::$app->getRequest()->post('name');
            $type    = \Yii::$app->getRequest()->post('type');
            $items = \Yii::$app->getRequest()->post('items', []);
            $auth = \Yii::$app->getAuthManager();
            $parent = $type == Item::TYPE_ROLE ? $auth->getRole($name) : $auth->getPermission($name);
            foreach ($items as $itemName){
                if (($item = $auth->getPermission($itemName)) == null){
                    $item = $auth->getRole($itemName);
                }
                $success = $auth->addChild($parent, $item);
            }
            $assigments = AuthItemX::getItemsXle($type, $name);

            $this->result =[
                'status' => true,
                'data'=>  $assigments
            ];
        } catch (\Exception $e) {
            $this->result['data'] = $e->getMessage();
        }
        return $this->asJson($this->result);
    }

    /**
     * +++ Удаление у итема ролей, разрешений, роутов
     * @param string $id
     * @param string $type (roles, permissions, routs)
     * @param array $items
     * @return string
     */
    public function actionRevoke()
    {
        try {
            $name    = \Yii::$app->getRequest()->post('name');
            $type    = \Yii::$app->getRequest()->post('type');
            $items = \Yii::$app->getRequest()->post('items', []);
            $auth = \Yii::$app->getAuthManager();
            $parent = $type == Item::TYPE_ROLE ? $auth->getRole($name) : $auth->getPermission($name);
            foreach ($items as $itemName){
                if (($item = $auth->getPermission($itemName)) == null){
                    $item = $auth->getRole($itemName);
                }
                $success = $auth->removeChild($parent, $item);
            }
            $assigments = AuthItemX::getItemsXle($type, $name);

            $this->result =[
                'status' => true,
                'data'=>  $assigments
            ];
        } catch (\Exception $e) {
            $this->result['data'] = $e->getMessage();
        }
        return $this->asJson($this->result);
    }




}
