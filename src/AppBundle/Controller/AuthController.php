<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Infrastructure\AbstractRestController;
use AppBundle\Exception\UserExists;
use AppBundle\Response\AlreadyExistsResponse;
use AppBundle\Response\MandatoryParameterMissedResponse;
use AppBundle\Response\NotAllowedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vehsamrak
 */
class AuthController extends AbstractRestController
{

    /**
     * @Route("/register", name="api_register_user")
     * @Method("POST")
     * @return JsonResponse
     */
    public function registerAction(Request $request)
    {
        $requestContentsJson = $request->getContent();
        $requestContents = json_decode($requestContentsJson, true);
        $requestParameters = $request->request->all();

        $email = $requestContents['email'] ?? $requestParameters['email'];
        $name = $requestContents['name'] ?? $requestParameters['name'];
        $flatNumber = $requestContents['flatNumber'] ?? $requestParameters['flatNumber'];

        if ($email && $name && $flatNumber) {
            $userRegistrator = $this->get('counter_card.user_registrator');

            try {
                $user = $userRegistrator->registerUser($email, $name, $flatNumber);
                $response = new JsonResponse($user->getToken());
            } catch (UserExists $exception) {
                $response = new AlreadyExistsResponse('User with this email of flat number already exists.');
            }
        } else {
            $response = new MandatoryParameterMissedResponse();
        }

        return $response;
    }

    /**
     * @Route("/login", name="api_login")
     * @Method("POST")
     * @return JsonResponse
     */
    public function loginAction(Request $request)
    {
        $requestContentsJson = $request->getContent();
        $requestContents = json_decode($requestContentsJson, true);
        $requestParameters = $request->request->all();

        $login = $requestContents['login'] ?? $requestParameters['login'] ?? null;
        $password = $requestContents['password'] ?? $requestParameters['password'] ?? null;

        if ($login && $password) {
            $userRepository = $this->get('counter_card.user_repository');
            $user = $userRepository->findOneByEmailAndPassword($login, $password);

            if (!$user) {
            	$result = new NotAllowedResponse();
            } else {
                $newToken = $this->get('id_generator')->generateString();
                $user->updateToken($newToken);
                $userRepository->flush($user);

                $result = $user->getToken();
            }
        } else {
            $result = new MandatoryParameterMissedResponse();
        }

        return $this->respond($result);
    }
}
