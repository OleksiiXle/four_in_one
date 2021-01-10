<?php

namespace apiadmin\modules\adminxx\controllers;

use apiadmin\modules\adminxx\grids\UsersGrid;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use common\helpers\Functions;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\conservation\models\Conservation;
use common\components\AccessControl;
use apiadmin\controllers\MainController;
use apiadmin\modules\adminxx\models\Assignment;
use apiadmin\modules\adminxx\models\form\ChangePassword;
use apiadmin\modules\adminxx\models\form\ForgetPassword;
use apiadmin\modules\adminxx\models\form\PasswordResetRequestForm;
use apiadmin\modules\adminxx\models\form\Update;
use common\models\UserM;
use yii\helpers\Url;
use yii\web\Response;

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
                    'actions' => ['error', 'forget-password', 'test', 'login', 'invitation-confirm'],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['error', 'test', 'change-password', 'update-profile', 'conservation', 'logout', 'invitation-confirm'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => [
                        'php-info', 'test',
                    ],
                    'roles' => ['menuAdminxMain'],
                ],
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view',
                        'export-to-exel-count', 'export-to-exel-get-partition', 'upload-report',
                    ],
                    'roles' => ['adminUsersView'],
                ],
                [
                    'allow' => true,
                    'actions' => [
                        'signup-by-admin', 'change-user-activity', 'update-by-admin',
                        'conservation', 'conserve-delete', 'delete-by-admin'
                    ],
                    'roles' => ['adminUserCreate', 'adminUserUpdate', 'adminSuper'],
                ],
                [
                    'allow' => true,
                    'actions' => [
                        'update-user-assignments',
                    ],
                    'roles' => ['adminChangeUserAssignments', 'adminUsersAdvanced'],
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
                'delete-by-admin' => ['post'],
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
        $usersGrid = new UsersGrid();
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            return $usersGrid->reload(Yii::$app->request->post());
        }
        return $this->render('index', [
            'usersGrid' => $usersGrid,
        ]);
    }

    /**
     * +++ Регистрация нового пользователя Администратором singup-by-admin
     * @return string
     */
    public function actionSignupByAdmin()
    {
        $model = new UserM();
        $model->scenario = UserM::SCENARIO_SIGNUP_BY_ADMIN;
        $defaultRoles = $model->defaultRoles;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->updateUser()) {
                $session = \Yii::$app->session;
                if ($session->get('searchIid')) {
                    $session->remove('searchIid');
                }
                $session->set('searchIid', $model->id);

                return $this->redirect(Url::toRoute('index'));
            }
        }

        //  return $this->render('updateUser', [
        return $this->render('signupByAdmin', [
            'model' => $model,
            'defaultRoles' => $defaultRoles,
            'userRoles' => [],
        ]);
    }

    /**
     * +++ Редактирование профиля пользователя администратором update-by-admin
     * @return string
     */
    public function actionUpdateByAdmin($mode, $id = 0, $invitation = false)
    {
        $model = UserM::findOne($id);
        $model->scenario = UserM::SCENARIO_UPDATE;

        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id);
        $userRoles = [];
        if (!empty($roles)) {
            foreach ($roles as $key => $role) {
                $userRoles[$key] = $role->description;
            }
        }
        $defaultRoles = $model->defaultRoles;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->updateUser()) {
                return $this->redirect(Url::toRoute('index'));
            }
        }

        return $this->render('update', [
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
     * Set new password
     * @return string
     */
    public function actionForgetPassword()
    {

        $model = new ForgetPassword();

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {// && $model->forgetPassword()
            $res = $model->forgetPassword();

            if ($res === null) {
                Yii::$app->getSession()->setFlash('userNotFound', 'User was not found.');
            } elseif ($res) {
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
    public function actionConservation($user_id)
    {
        $conservationJson = Conservation::find()
            ->where(['user_id' => $user_id])
            ->asArray()
            ->all();
        $conservation = ((isset($conservationJson[0]['conservation'])))
            ? json_decode($conservationJson[0]['conservation'], true)
            : [];
        return $this->render('conservation', [
                'conservation' => $conservation,
                'user_id' => $user_id]
        );
    }

    public function actionConserveDelete($user_id)
    {
        if (Yii::$app->request->isPost) {
            $del = Conservation::deleteAll(['user_id' => $user_id]);
        }
        return $this->redirect(Url::toRoute('/adminxx/user'));
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

    public function actionInvitationConfirm($token)
    {
        $user = new UserM();
        try {
            if ($user->confirmation($token)) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Регистрация успешно подтверждена'));
                return $this->redirect($this->getUserLoginUrl());

            }
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->goHome();

    }

    private function getUserLoginUrl()
    {
        $adminHostName = Yii::$app->params['adminHostName'];
        $userHostName = Yii::$app->params['userHostName'];
        $absoluteBaseUrl = Url::base(true);

        return str_replace($adminHostName, $userHostName, $absoluteBaseUrl) . '/site/login';
    }

    /**
     * @return string
     */
    public function actionTest()
    {
        //    $this->layout = '@app/modules/adminxx/views/layouts/testLayout.php';
        //  $this->layout = false;
        $tmp = 1;
        $ret = UserM::find()
            ->where(['id' => 1])
            ->all();
        return $this->render('test', ['ret' => $ret]);
    }

    /**
     * Удаление профиля пользователя администратором
     * @return string
     */
    public function actionDeleteByAdmin($id)
    {
        if (\Yii::$app->request->isPost) {
            $userDel = UserM::findOne($id)->delete();
            if ($userDel === 0) {
                \yii::$app->getSession()->addFlash("warning", "Ошибка при удалении.");
            }
        }
        return $this->redirect(Url::toRoute('index'));

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
        $_post = \yii::$app->request->post();
        if (isset($_post['user_id'])) {
            $user = UserM::findOne($_post['user_id']);
            if (isset($user)) {
                switch ($user->status) {
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
                if (!$ret) {
                    $response['data'] = $user->showErrors();
                } else {
                    $response['status'] = true;
                }
            }
        }
        return json_encode($response);

    }


    //******************************************************************************************* ВЫВОД СПИСКА В ФАЙЛ

    /**
     * +++ 1. АЯКС Вывод в EXEL данных из таблицы пользователей с учетом фильтра   (определение количества записей) export-to-exel-count
     * @return string
     */
    public function actionExportToExelCount()
    {
        try {
            $_post = \Yii::$app->request->post();
            if (isset($_post['exportQuery'])) {
                $userId = \Yii::$app->user->getId();
                $fileFullName = \Yii::getAlias('@app/web/tmp/report_') . $userId . '.xls';

                if (file_exists($fileFullName)) {
                    unlink($fileFullName);
                }
                $exportQuery = [
                    'filterModelClass' => $_post['exportQuery']['filterModelClass'],
                    'filter' => json_decode($_post['exportQuery']['filter'], true),
                    'sort' => json_decode($_post['exportQuery']['sort'], true),
                ];
                $query = new $exportQuery['filterModelClass'];
                if (!empty($exportQuery['filter'])) {
                    $query->setAttributes($exportQuery['filter']);
                }
                $ret = $query->getQuery();
                $this->result['data'] = $ret->count();
                $this->result['status'] = true;
            }
        } catch (\Exception $e) {
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
        try {
            $_post = \Yii::$app->request->post();
            if (isset($_post['exportQuery']) && isset($_post['limit']) && isset($_post['offset'])) {
                $userId = \Yii::$app->user->getId();
                $fileFullName = \Yii::getAlias('@app/web/tmp/report_') . $userId . '.xls';


                $exportQuery = [
                    'filterModelClass' => $_post['exportQuery']['filterModelClass'],
                    'filter' => json_decode($_post['exportQuery']['filter'], true),
                    'sort' => json_decode($_post['exportQuery']['sort'], true),
                ];
                $query = new $exportQuery['filterModelClass'];
                if (!empty($exportQuery['filter'])) {
                    $query->setAttributes($exportQuery['filter']);
                }
                $ret = $query->getQuery();
                if (!empty($exportQuery['sort'])) {
                    $ret->addOrderBy($exportQuery['sort']);
                }
                $users = $ret
                    ->limit($_post['limit'])
                    ->offset($_post['offset'])
                    ->all();
                if (!empty($users)) {
                    foreach ($users as $user) {

                        $result[] = $user->userProfileStrShort;
                    }
                    $this->result = Functions::exportToExelUniversal($result, $fileFullName, 'Список', false);
                }
            }
        } catch (\Exception $e) {
            $this->result['data'] = $e->getMessage();
        }
        return json_encode($this->result);
    }

    /**
     * +++ 3. АЯКС вывод собранного файла upload-report
     * @return array
     */
    public function actionUploadReport()
    {
        $userId = \Yii::$app->user->getId();

        $pathToFile = \Yii::getAlias('@app/web/tmp/report_') . $userId . '.xls';

        $ret = Functions::uploadFileXle($pathToFile, true);
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
        if (isset($_get['exportQuery'])) {
            $exportQuery = $_get['exportQuery'];
        } elseif (isset($_post['exportQuery'])) {
            $exportQuery = $_post['exportQuery'];
        } else {
            $exportQuery = [];
        }
        if (!empty($exportQuery)) {
            $query = new $exportQuery['filterModelClass'];
            if (!empty($exportQuery['filter'])) {
                $query->setAttributes($exportQuery['filter']);
            }
            $ret = $query->getQuery();
            if (!empty($exportQuery['sort'])) {
                $ret->addOrderBy($exportQuery['sort']);
            }
            $users = $ret->all();
            if (!empty($users)) {
                foreach ($users as $user) {

                    $result[] = $user->userProfileStrShort;
                }
                $pathToFile = \Yii::getAlias('@app/web/tmp');
                $userId = \Yii::$app->user->getId();
                Functions::exportToExel($result, $pathToFile, $userId, 'report_');
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
        try {
            $_post = \Yii::$app->request->post();
            if (isset($_post['exportQuery'])) {
                $exportQuery = [
                    'filterModelClass' => $_post['exportQuery']['filterModelClass'],
                    'filter' => json_decode($_post['exportQuery']['filter'], true),
                    'sort' => json_decode($_post['exportQuery']['sort'], true),
                ];
                $query = new $exportQuery['filterModelClass'];
                if (!empty($exportQuery['filter'])) {
                    $query->setAttributes($exportQuery['filter']);
                }
                $ret = $query->getQuery();
                if (!empty($exportQuery['sort'])) {
                    $ret->addOrderBy($exportQuery['sort']);
                }
                $users = $ret->all();
                if (!empty($users)) {
                    foreach ($users as $user) {

                        $result[] = $user->userProfileStrShort;
                    }
                    $pathToFile = \Yii::getAlias('@app/web/tmp');
                    $userId = \Yii::$app->user->getId();
                    $this->result = Functions::exportToExel($result, $pathToFile, $userId, 'report_', 'Список', false);
                }
            }
        } catch (\Exception $e) {
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
        if (!empty($id)) {
            $model = Update::findOne($id);
            $model->first_name = $model->userDatas->first_name;
            $model->middle_name = $model->userDatas->middle_name;
            $model->last_name = $model->userDatas->last_name;

            if (\Yii::$app->getRequest()->isPost) {
                $data = \Yii::$app->getRequest()->post('Update');
                $model->setAttributes($data);
                $model->first_name = $data['first_name'];
                $model->middle_name = $data['middle_name'];
                $model->last_name = $data['last_name'];

                if ($model->updateUser()) {
                    return $this->goHome();
                }
            }

            return $this->render('updateProfile', [
                'model' => $model,
                'user_id' => $id,

            ]);
        } else {
            \yii::$app->getSession()->addFlash("warning", "Неверный ИД пользователя");
            return $this->redirect(\Yii::$app->request->referrer);

        }
    }
}