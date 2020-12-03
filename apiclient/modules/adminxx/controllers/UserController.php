<?php

namespace app\modules\adminxx\controllers;

use Yii;
use app\helpers\Functions;
use app\components\UserProfile;
use app\components\conservation\ActiveDataProviderConserve;
use app\components\conservation\models\Conservation;
use app\components\AccessControl;
use app\modules\adminxx\models\Assignment;
use app\modules\adminxx\models\filters\UserFilter;
use app\modules\adminxx\models\form\ChangePassword;
use app\modules\adminxx\models\form\ForgetPassword;
use app\modules\adminxx\models\form\Login;
use app\modules\adminxx\models\form\PasswordResetRequestForm;
use app\modules\adminxx\models\form\Update;
use app\modules\adminxx\models\UserM;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * Class UserController
 * Управление пользователями
 * @package app\modules\adminxx\controllers
 */
class UserController extends MainController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [ 'forget-password', 'test', 'login' ],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['test', 'change-password', 'update-profile', 'conservation', 'logout'],
                    'roles' => ['@'],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'php-info', 'test' ,
                    ],
                    'roles'      => ['menuAdminxMain'],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'index', 'view',
                        'export-to-exel-count', 'export-to-exel-get-partition', 'upload-report', 'get-department-full-name',
                        'get-department-name'
                    ],
                    'roles'      => ['adminUsersView'],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'signup-by-admin', 'change-user-activity', 'update-by-admin',
                        'get-personal-data', 'get-personal-data-by-id', 'get-personal-data-by-fio', 'get-department-name'
                    ],
                    'roles'      => ['adminUserCreate', 'adminUserUpdate' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'update-user-assignments',
                    ],
                    'roles'      => ['adminChangeUserAssignments'],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'change-sort',
                    ],
                    'roles'      => ['_orgStatChangeSort'],
                ],
            ],
            /*
            'denyCallback' => function ($rule, $action) {
            if (\Yii::$app->user->isGuest){
                $redirect = Url::toRoute(\Yii::$app->user->loginUrl);
                return $this->redirect( $redirect);
            } else {
                \yii::$app->getSession()->addFlash("warning",\Yii::t('app', "Действие запрещено"));
                return $this->redirect(\Yii::$app->request->referrer);
            }
        }
            */
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['post'],
                'logout' => ['post'],
                'activate' => ['post'],
            ],

        ];
        return $behaviors;
    }

    /**
     * +++ Список всех пользователей index
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
      //  $this->layout = '@app/modules/adminxx/views/layouts/adminxx.php';

        $dataProvider = new ActiveDataProviderConserve([
           // 'searchId' => $id,
            'filterModelClass' => UserFilter::class,
            'conserveName' => 'userAdminGrid',
            'pageSize' => 5,
            'sort' => ['attributes' => [
                'id',
                'username',
                'nameFam' => [
                    'asc' => [
                        'user_data.last_name' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_name' => SORT_DESC,
                    ],
                ],
                'lastRoutTime' => [
                    'asc' => [
                        'user_data.last_rout_time' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout_time' => SORT_DESC,
                    ],
                ],
                'lastRout' => [
                    'asc' => [
                        'user_data.last_rout' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout' => SORT_DESC,
                    ],
                ],
                'status' => [
                    'asc' => [
                        'user.status' => SORT_ASC,
                    ],
                    'desc' => [
                        'user.status' => SORT_DESC,
                    ],
                ],
            ]],

        ]);
        $r=1;
        /*
        if (\Yii::$app->request->isPost){
            return $this->redirect('index');
        }
        */
        return $this->render('index',[
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * +++ Регистрация нового пользователя Администратором singup-by-admin
     * @return string
     */
    public function actionSignupByAdmin($invitation = false)
    {
        $model = new UserM();
        $model->scenario = UserM::SCENARIO_SIGNUP_BY_ADMIN;
        $defaultRoles = $model->defaultRoles;
        if ($model->load(Yii::$app->request->post())) {
            $tmp = json_decode($model->userRolesToSet, true);
            if ($model->updateUser()) {
                $session = \Yii::$app->session;
                if ($session->get('searchIid')){
                    $session->remove('searchIid');
                }
                $session->set('searchIid', $model->id );

                return $this->redirect(Url::toRoute('index'));
            }
        }

      //  return $this->render('updateUser', [
        return $this->render('signupByAdmin', [
            'model' => $model,
            'defaultRoles' => $defaultRoles,
            'userDepartments' => [],
            'userRoles' => [],
        ]);
    }







    /**
     * +++ Редактирование профиля пользователя администратором update-by-admin
     * @return string
     */
    public function actionUpdateByAdmin($id)
    {
        $model = UserM::findOne($id);
        $model->scenario = UserM::SCENARIO_UPDATE;

        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id);
        $userRoles = [];
        if (!empty($roles)){
            foreach ($roles as $key => $role){
                $userRoles[] = [
                    'id' => $key,
                    'name' => $role->description,
                ];
            }
        }
        $defaultRoles = $model->defaultRoles;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->updateUser()) {
                return $this->redirect(Url::toRoute('index'));
            }
        }

        return $this->render('updateUser', [
            'model' => $model,
            'userRoles' => $userRoles,
            'defaultRoles' => $defaultRoles,
        ]);
    }

    /**
     * +++ Просмотр профиля пользователя view
     * @return string
     */
    public function actionView($id)
    {
        $user = UserM::findOne($id);
        $userProfile = $user->userProfile;
        return $this->render('view', [
            'userProfile' => $userProfile,
        ]);
    }

    /**
     * +++ Нестандартное редактирование разрешений и ролей пользователя администратором update-user-assignments
     * @return string
     */
    public function actionUpdateUserAssignments($id)
    {
        $model = UserM::findOne($id);
        $model->scenario = UserM::SCENARIO_UPDATE;
        $ass = new Assignment($id);
        $assigments = $ass->getItemsXle();
        if (\Yii::$app->getRequest()->isPost) {
            $data = \Yii::$app->getRequest()->post('UserM');
            $ret = ($data['status'] == UserM::STATUS_INACTIVE) ? $model->deactivate() : $model->activate();
            if ($ret) {
                return $this->redirect(Url::toRoute('/adminxx/user'));
            }
        }

        return $this->render('updateUserAssignments', [
            'model' => $model,
            'user_id' => $id,
            'assigments' => $assigments,

        ]);
    }

    /**
     * +++ Login
     * @return string
     */
    public function actionLogin()
    {
      //  $this->layout = '@app/views/layouts/commonLayout.php';

        $model = new Login();
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            $query = 'SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""))';
            Yii::$app->db->createCommand($query)->execute();

            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     *+++ Logout
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->userProfile->language = Yii::$app->language;

        \Yii::$app->getUser()->logout();
        return $this->goHome();
     //   return $this->redirect('/site/index');
    }

    /**
     * Change password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }
        return $this->render('changePassword', [
            'model' => $model,
        ]);
    }

    /**
     * Set new password
     * @return string
     */
    public function actionForgetPassword()
    {

        $model = new ForgetPassword();

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {// && $model->forgetPassword()
            $res = $model->forgetPassword();

            if($res===null){
                Yii::$app->getSession()->setFlash('userNotFound', 'User was not found.');
            }elseif($res){
                Yii::$app->getSession()->setFlash('newPwdSended', 'New password was sended.');
            }
        }

        return $this->render('forgetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionConservation($id)
    {
        $conservationJson = Conservation::find()
            ->where(['user_id' => $id])
            ->asArray()
            ->all();
        $conservation = ((isset($conservationJson[0]['conservation'])))
            ? json_decode($conservationJson[0]['conservation'], true)
            : [];
        return $this->render('conservation' , ['conservation' => $conservation]);
    }

    /**
     * @return string
     */
    public function actionPhpInfo()
    {
        return $this->render('phpinfo');
    }

    /**
     * ??? Запрос на смену пароля через Емейл request-password-reset
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                \Yii::$app->session->setFlash('success',
                    \Yii::t('app', 'На Ваш електронный адрес отправлено письмо для изменения пароля'));
            } else {
                \Yii::$app->session->setFlash('error', \Yii::t('app', 'Не удалось сбросить пароль с помощью Email'));
            }
            return $this->goHome();
        }

        return $this->render('passwordResetRequest', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function actionTest()
    {
        $this->layout = '@app/modules/adminxx/views/layouts/testLayout.php';
      //  $this->layout = false;
        $t = 1;
        return $this->render('test');
    }

    public function actionChangeSort()
    {
        $this->layout = '@app/views/layouts/commonLayout.php';

        $model = new UserProfile();

        if ($model->load(Yii::$app->request->post())) {
            $model->save();
        }

        return $this->render('changeSort', [
            'model' => $model,
        ]);


    }


    //******************** АЯКС

    /**
     * +++ Изменение активности пользователя change-user-activity
     * @return false|string
     */
    public function actionChangeUserActivity()
    {
        $response['status'] = false;
        $response['data'] = ['Данні не знайдено'];
        $_post    = \yii::$app->request->post();
        if (isset($_post['user_id'])) {
            $user = UserM::findOne($_post['user_id']);
            if (isset($user)){
                switch ($user->status){
                    case UserM::STATUS_ACTIVE:
                        $ret = $user->deactivate();
                        $response['data'] = 'inactive';
                        break;
                    case UserM::STATUS_INACTIVE:
                        $ret = $user->activate();
                        $response['data'] = 'active';
                        break;
                    default:
                        $response['data'] = 'Невірний статус';
                        return json_encode($response);
                }
                if (!$ret){
                    $response['data'] = $user->showErrors();
                } else {
                    $response['status'] = true;
                }
            }
        }
        return json_encode($response);

    }

    /**
     * +++ АЯКС- получение данных пользователя по жетону get-personal-data
     * @return false|string
     */
    public function actionGetPersonalData(){
        // 0082166
        $response['status'] = false;
        $response['data'] = ['Данні не знайдено'];
        $_post    = \yii::$app->request->post();
        if (isset($_post['spec_document'])){
            $spec_document = $_post['spec_document'];
        }
        if (isset($spec_document)){
            $personal = PersonalCommon::find()
                ->where(['spec_document' => $spec_document])
                ->one();
            if (isset($personal)){
                $personal_id = $personal->id;
                $positionFullName =$personal->positionCommon->revertSemiFullName;
                $response['data'] = [
                    'personal_id' => $personal_id,
                    'positionFullName' => $positionFullName,
                    'name_family' => $personal->name_family,
                    'name_first' => $personal->name_first,
                    'name_last' => $personal->name_last,
                ];
                $response['status'] = true;
            }
        }
        return json_encode($response);

    }

    /**
     *  +++ АЯКС- получение данных пользователя по ИД get-personal-data-by-id
     * @return false|string
     */
    public function actionGetPersonalDataById(){
        // 0082166
        $response['status'] = false;
        $response['data'] = ['Данні не знайдено'];
        $_post    = \yii::$app->request->post();
        if (isset($_post['id'])){
            $id = $_post['id'];
        }
        if (isset($id)){
            $personal = PersonalCommon::find()
                ->where(['id' => $id])
                ->one();
            if (isset($personal)){
                $personal_id = $personal->id;
                $positionFullName =$personal->positionCommon->revertSemiFullName;
                $response['data'] = [
                    'personal_id' => $personal_id,
                    'positionFullName' => $positionFullName,
                    'name_family' => $personal->name_family,
                    'name_first' => $personal->name_first,
                    'name_last' => $personal->name_last,
                    'spec_document' => $personal->spec_document,
                ];
                $response['status'] = true;
            }
        }
        return json_encode($response);

    }

    /**
     *  +++ АЯКС- получение данных пользователя по ФИО get-personal-data-by-fio
     * @return false|string
     */
    public function actionGetPersonalDataByFio(){
        /*
                    'last_name': last_name,
            'first_name': first_name,
            'middle_name': middle_name

         */
        // 0082166
        $response['status'] = false;
        $response['data'] = ['Данні не знайдено'];
        $_post    = \yii::$app->request->post();
        if (isset($_post['last_name']) && !empty($_post['last_name'])){
            $last_name = $_post['last_name'];
        }
        if (isset($last_name)){
           // $query = PersonalCommon::find()
            $query = (new Query())
                ->select('id, name_family, name_first, name_last, ')
                ->from('personal')
                ->where(['name_family' => $last_name]);
            if (isset($_post['first_name']) && !empty($_post['first_name'])){
                $first_name = $_post['first_name'];
                $query->andWhere(['name_first' => $first_name]);
            }
            if (isset($_post['middle_name']) && !empty($_post['middle_name'])){
                $middle_name= $_post['middle_name'];
                $query->andWhere(['name_last' => $middle_name]);
            }
            $rr = 1;
            $personal = $query
                ->orderBy('name_first')
                ->all();
            if (!empty($personal)){
                $response['data'] = [];
           //    foreach ($personal as $persona){
                for ($i = 0; $i < count($personal); $i++){
                    $persona = PersonalCommon::find()
                    ->where(['id' => $personal[$i]['id']])
                    ->one();
                    $personal_id = $persona->id;
                    $positionFullName =$persona->positionCommon->revertSemiFullName;;
                    $response['data'][] =
                        [
                            'id' => $personal_id,
                            'name' => $persona->name_family
                                . ' ' . $persona->name_first . ' ' . $persona->name_last
                                . ' *** ' . $positionFullName . ' *** ' ]
                        ;

                }
                $response['status'] = true;
            }
        }
        return json_encode($response);

    }

    /**
     *  +++ АЯКС- получение реверсного полного названия подразделения по $department_id get-department-full-name
     * @return false|string
     */
    public function actionGetDepartmentFullName(){
        $response['status'] = false;
        $response['data'] = ['Данні не знайдено'];
        $_post    = \yii::$app->request->post();
        if (isset($_post['department_id'])){
            $department_id = $_post['department_id'];
        }
        if (isset($department_id)){
            $department = DepartmentCommon::findOne($department_id);
            if (isset($department)){
                $response['status'] = true;
                $response['data'] = $department->getFullNameRevert(DepartmentCommon::ROOT_ID);
            }
        }
        return json_encode($response);

    }

    /**
     *  +++ АЯКС- получение полного названия подразделения по $department_id get-department-name
     * @return string
     */
    public function actionGetDepartmentName($department_id = 0)
    {
        $department = DepartmentCommon::findOne($department_id);
        if (isset($department)){
            $this->result['status'] = true;
            $this->result['data'] = $department->gunpName;
        }
        return $this->asJson($this->result);
    }

    //******************************************************************************************* ВЫВОД СПИСКА В ФАЙЛ

    /**
     * +++ 1. АЯКС Вывод в EXEL данных из таблицы пользователей с учетом фильтра   (определение количества записей) export-to-exel-count
     * @return string
     */
    public function actionExportToExelCount()
    {
        try{
            $_post = \Yii::$app->request->post();
            if (isset($_post['exportQuery'])){
                $userId = \Yii::$app->user->getId();
                $fileFullName = \Yii::getAlias('@app/web/tmp/report_') . $userId . '.xls';

                if (file_exists($fileFullName)){
                    unlink($fileFullName);
                }
                $exportQuery = [
                    'filterModelClass' => $_post['exportQuery']['filterModelClass'],
                    'filter' => json_decode($_post['exportQuery']['filter'], true),
                    'sort' => json_decode($_post['exportQuery']['sort'], true),
                ];
                $query = new $exportQuery['filterModelClass'];
                if (!empty($exportQuery['filter'])){
                    $query->setAttributes($exportQuery['filter']);
                }
                $ret = $query->getQuery();
                $this->result['data'] = $ret->count();
                $this->result['status'] = true;
            }
        } catch (\Exception $e){
            $this->result['data'] = $e->getMessage();
        }
        return json_encode($this->result);
    }

    /**
     * +++ 2. АЯКС Вывод в EXEL (запись куска во врем файл с добавлением, $_post['limit'] $_post['offset']) export-to-exel-get-partition
     * @return string
     */
    public function actionExportToExelGetPartition()
    {
        try{
            $_post = \Yii::$app->request->post();
            if (isset($_post['exportQuery']) && isset($_post['limit']) && isset($_post['offset'])){
                $userId = \Yii::$app->user->getId();
                $fileFullName = \Yii::getAlias('@app/web/tmp/report_') . $userId . '.xls';


                $exportQuery = [
                    'filterModelClass' => $_post['exportQuery']['filterModelClass'],
                    'filter' => json_decode($_post['exportQuery']['filter'], true),
                    'sort' => json_decode($_post['exportQuery']['sort'], true),
                ];
                $query = new $exportQuery['filterModelClass'];
                if (!empty($exportQuery['filter'])){
                    $query->setAttributes($exportQuery['filter']);
                }
                $ret = $query->getQuery();
                if (!empty($exportQuery['sort'])){
                    $ret->addOrderBy($exportQuery['sort']);
                }
                $users = $ret
                    ->limit($_post['limit'])
                    ->offset($_post['offset'])
                    ->all();
                if (!empty($users)){
                    foreach ($users as $user){

                        $result[]= $user->userProfileStrShort;
                    }
                    $this->result = Functions::exportToExelUniversal($result, $fileFullName,  'Список', false );
                }
            }
        } catch (\Exception $e){
            $this->result['data'] = $e->getMessage();
        }
        return json_encode($this->result);
    }

    /**
     * +++ 3. АЯКС вывод собранного файла upload-report
     * @return array
     */
    public function actionUploadReport(){
        $userId = \Yii::$app->user->getId();

        $pathToFile = \Yii::getAlias('@app/web/tmp/report_') . $userId . '.xls';

        $ret = Functions::uploadFileXle($pathToFile,true);
        return $ret;
    }


    //******************************************************************************************* НЕ ИСПОЛЬЗУЮТСЯ

    /**
     * --- АЯКС Вывод в EXEL данных из таблицы пользователей с учетом фильтра
     * @return string
     */
    public function actionExportToExel()
    {
        $_get = \Yii::$app->request->get();
        $_post = \Yii::$app->request->post();
        if (isset($_get['exportQuery'])){
            $exportQuery = $_get['exportQuery'];
        } elseif (isset($_post['exportQuery'])){
            $exportQuery = $_post['exportQuery'];
        } else {
            $exportQuery = [];
        }
        if (!empty($exportQuery)){
            $query = new $exportQuery['filterModelClass'];
            if (!empty($exportQuery['filter'])){
                $query->setAttributes($exportQuery['filter']);
            }
            $ret = $query->getQuery();
            if (!empty($exportQuery['sort'])){
                $ret->addOrderBy($exportQuery['sort']);
            }
            $users = $ret->all();
            if (!empty($users)){
                foreach ($users as $user){

                    $result[]= $user->userProfileStrShort;
                }
                $pathToFile = \Yii::getAlias('@app/web/tmp');
                $userId = \Yii::$app->user->getId();
                Functions::exportToExel($result, $pathToFile, $userId, 'report_' );
                return true;
            }
        }
        return $this->redirect(Url::toRoute('index'));
    }

    /**
     * --- АЯКС Вывод в EXEL данных из таблицы пользователей с учетом фильтра   (подготовка временного файла)
     * @return string
     */
    public function actionExportToExelPrepare()
    {
        ini_set("memory_limit", "512M");
        try{
            $_post = \Yii::$app->request->post();
            if (isset($_post['exportQuery'])){
                $exportQuery = [
                    'filterModelClass' => $_post['exportQuery']['filterModelClass'],
                    'filter' => json_decode($_post['exportQuery']['filter'], true),
                    'sort' => json_decode($_post['exportQuery']['sort'], true),
                ];
                $query = new $exportQuery['filterModelClass'];
                if (!empty($exportQuery['filter'])){
                    $query->setAttributes($exportQuery['filter']);
                }
                $ret = $query->getQuery();
                if (!empty($exportQuery['sort'])){
                    $ret->addOrderBy($exportQuery['sort']);
                }
                $users = $ret->all();
                if (!empty($users)){
                    foreach ($users as $user){

                        $result[]= $user->userProfileStrShort;
                    }
                    $pathToFile = \Yii::getAlias('@app/web/tmp');
                    $userId = \Yii::$app->user->getId();
                    $this->result = Functions::exportToExel($result, $pathToFile, $userId, 'report_', 'Список', false );
                }
            }
        } catch (\Exception $e){
            $this->result['data'] = $e->getMessage();
        }
        return json_encode($this->result);
    }

    /**
     * --- Редактирование профиля пользователя пользователем update-profile
     * @return string
     */
    public function actionUpdateProfile()
    {
        $id = \Yii::$app->user->getId();
        if (!empty($id)){
            $model = Update::findOne($id);
            $model->first_name = $model->userDatas->first_name;
            $model->middle_name = $model->userDatas->middle_name;
            $model->last_name = $model->userDatas->last_name;

            if (\Yii::$app->getRequest()->isPost) {
                $data = \Yii::$app->getRequest()->post('Update');
                $model->setAttributes($data);
                $model->first_name = $data['first_name'];
                $model->middle_name =  $data['middle_name'];
                $model->last_name =  $data['last_name'];

                if ($model->updateUser()) {
                    return $this->goHome();
                }
            }

            return $this->render('updateProfile', [
                'model' => $model,
                'user_id' => $id,

            ]);
        } else {
            \yii::$app->getSession()->addFlash("warning","Неверный ИД пользователя");
            return $this->redirect(\Yii::$app->request->referrer);

        }
    }

    /**
     * --- Удаление профиля пользователя
     * @return string
     */
    public function actionDelete($id)
    {
        if (\Yii::$app->request->isPost){
            $userDel = UserM::findOne($id)->delete();
            if ($userDel === 0){
                \yii::$app->getSession()->addFlash("warning","Ошибка при удалении.");
            }
        }
        return $this->redirect(Url::toRoute('index'));

    }

}