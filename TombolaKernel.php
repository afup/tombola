<?php

class TombolaKernel extends \Symfony\Component\HttpKernel\Kernel {
    use \Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

    private $auth;

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle()
        ];
    }

    protected function configureRoutes(\Symfony\Component\Routing\RouteCollectionBuilder $routes)
    {
        $routes->add('/login', 'kernel:loginAction', 'login');
        $routes->add('/callback', 'kernel:callbackAction', 'callback');
        $routes->add('/tombola', 'kernel:tombolaAction', 'tombola');
        $routes->add('/admin', 'kernel:adminAction', 'admin');
    }

    protected function configureContainer(\Symfony\Component\DependencyInjection\ContainerBuilder $c, \Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $c
            ->loadFromExtension('framework', [
                'secret' => 'micr0',
                'session' => [
                    'handler_id' => 'session.handler.native_file',
                    'save_path' => "%kernel.root_dir%/sessions",
                ],
                'templating' => ['engines' => ['twig']],
            ])
        ;
    }

    public function loginAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $authUrl = $this->getAuth()->getAuthorizationUrl();
        $session = $request->getSession();

        $session->start();
        $session->set('oauth2state', $this->getAuth()->getState());

        return new \Symfony\Component\HttpFoundation\RedirectResponse($authUrl);
    }

    public function callbackAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $session = $request->getSession();
        $session->start();
        if ($request->query->get('state') !== $session->get('oauth2state')) {
            $session->remove('oauth2state');

            return new \Symfony\Component\HttpFoundation\RedirectResponse('/');
        }

        $token = $this->getAuth()->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        try {
            $user = $this->getAuth()->getResourceOwner($token);$data = $user->toArray();
            $userData = [
                'provider' => 'github',
                'id' => $user->getId(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'email' => $user->getEmail(),
                'avatar' => isset($data['avatar_url']) ? $data['avatar_url'] : null
            ];

            $userData['access-token'] = $token->getToken();
            $userData['refresh-token'] = $token->getRefreshToken();

            $session->set('user', $userData);
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        $redirect = '/tombola';
        if ($userData['nickname'] === 'xavierleune') {
            //Todo check if the user has write access to afup/web
            $redirect = '/admin';
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse($redirect);
    }

    public function adminAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $user = $request->getSession()->get('user');
        if ($user['nickname'] !== 'xavierleune') {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        return new \Symfony\Component\HttpFoundation\Response(
            $this->getContainer()->get('templating')->render(
                dirname(__FILE__) . '/templates/admin.html.twig',
                ['avatar' => $user['avatar']]
            )
        );
    }

    /**
     * @return \League\OAuth2\Client\Provider\Github
     */
    private function getAuth()
    {
        if ($this->auth === null) {
            $callbackUrl = $this->getContainer()->get('router')->generate('callback', [], \Symfony\Component\Routing\Router::ABSOLUTE_URL);

            $this->auth = new \League\OAuth2\Client\Provider\Github([
                'clientId' => getenv('GITHUB_CLIENT_ID'),
                'clientSecret' => getenv('GITHUB_CLIENT_SECRET'),
                'redirectUri' => $callbackUrl,
            ]);
        }

        return $this->auth;
    }

}
