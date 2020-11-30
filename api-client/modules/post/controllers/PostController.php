<?php

namespace app\modules\post\controllers;

use app\modules\post\models\Post;
use Yii;
use app\components\AccessControl;
use yii\filters\VerbFilter;
use yii\httpclient\Client;
use yii\web\NotFoundHttpException;
use app\components\AuthHandler;

class PostController extends MainController
{
    protected $_xapi;

    public function beforeAction($action)
    {
        $apiProvider = \Yii::$app->authClientCollection->getClient('xapi')->fullClientId;

        Yii::$app->configs->apiProvider = $apiProvider;

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index', 'create', 'view', 'delete',
                    ],
                    'roles'      => ['@', '?', ],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
                'authclient' => 'xapi',
            ],
        ];
    }

    private function checkApiAuthorization($response)
    {
        switch ($response['authError']) {
            case 'Authorization required':
                return $this->redirect($this->xapi->authRedirect);
                break;
            case 'Access denied':
                $session = Yii::$app->session;
                $session->setFlash('error', $response['data']['message']);
                return $this->goBack();
                break;
        }
    }

    public function onAuthSuccess($client)
    {
        (new AuthHandler($client))->handle();
    }

    public function getXapi()
    {
        $tmp = 1;
        if (!($this->_xapi instanceof \app\components\XapiV1Client)) {
            $this->_xapi = clone \Yii::$app->xapi;
        }
        return $this->_xapi;
    }

    protected function checkResponse($response)
    {
        if (!$response->isOk && (!empty($response->data['message']))){
            $session = Yii::$app->session;
            $session->setFlash('danger', $response->data['message']);
        }
    }

    public function actionIndex()
    {
        $t=1;
        $response = $this->getXapi()->callMethod('/post/index', []);
        $this->checkApiAuthorization($response);
        return $this->render('index',[
            //'postsList' => $response['data'],
            'response' => $response,
        ]);

        if ($response['status']){
            return $this->render('index',[
                'postsList' => $response['data'],
                'response' => $response,
            ]);
        } else {
              $session = Yii::$app->session;
              $session->setFlash('error', $response['data']);
            return $this->goBack();

        }
    }

    public function actionCreate()
    {
        $post = new Post(\Yii::$app->xapi);
        if (\Yii::$app->request->isPost) {
            $_post = \Yii::$app->request->post();
            if (isset($_post['reset-button'])) {
                return $this->redirect('index');
            }
            $post->setAttributes($_post);

            if ($post->save()) {
                return $this->redirect('index');
            } else {
                $this->checkApiAuthorization($post->response);
            }
        }
        return $this->render('create', [
            'model' => $post,
        ]);
    }

    public function actionUpdate($id)
    {
        if (\Yii::$app->request->isPost) {

        } else {

        }

        return $this->render('create', [
            'model' => $post,
        ]);
    }

    public function actionChoiseSeats($seansId)
    {
        $t=1;
        if (!\Yii::$app->request->isPost){
            //  $client = new Client();
            $response = $this->getXapi()->callMethod('/sale/get-seans', ['id' => $seansId]);
            //      return $this->render('debug' , ['response' => $response] );
            if ($response['status']){
                return $this->render('seans',[
                    'seans' => $response['data'],
                ]);
            } else {
                //   $session = Yii::$app->session;
                //   $session->setFlash('error', $response['data']);
                return $this->goBack();

            }

            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('http://api.server/v1/sale/get-seans')
                ->setData(['id' => $seansId])
                ->send();
            $seans = $response->isOk ? $response->data : [];
            $result['data'] = $response->data;
            $result['code'] = $response->headers['http-code'];
            $result['headers'] = $response->headers;
            return $this->render('seans',[
                'seans' => $seans,
                'response' => $response,
                'result' => $result,
            ]);

        } else {
            $_post = \Yii::$app->request->post();
            if (isset($_post['reservation'])){
                $datas = json_decode($_post['reservation'], true);
                if (!empty($datas)){
                    return $this->redirect(['/seans/make-reservation',
                        'seansId' => $seansId,
                        'reservation' => $_post['reservation'],
                    ]);
                }
                return $this->redirect('/seans/seanses-list');
            } else {
                throw new NotFoundHttpException('Сеанс не найден');
            }
        }
    }

    public function actionMakeReservation($seansId, $reservation)
    {
        $datas = json_decode($reservation, true);
        foreach ($datas as $data){
            $buf = json_decode($data, true);
            $myReservation[] = [
                'rowNumber' => $buf['rowNumber'],
                'seatNumber' => $buf['seatNumber'],
                'persona' => 'lokoko',
            ];
        }
        if (!empty($myReservation)){
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl('http://api.server/v1/sale/get-reservation')
                ->setData(['seansId' => $seansId, 'reservation' => $myReservation] )
                ->send();
            $this->checkResponse($response);
            //    return $this->render('debug' , ['response' => $response] );
            if ($response->isOk){
                return $this->render('seansSuccessMessage', ['reservation' => $response->data]);
            } else {
                return $this->redirect(['/seans/choise-seats', 'seansId' => $seansId]);
            }
        } else {
            throw new NotFoundHttpException('Данные пусты');
        }

    }



}
