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
        $mysqli = new \Symfony\Component\DependencyInjection\Definition(
            \mysqli::class,
            [
                getenv('MYSQL_HOST'),
                getenv('MYSQL_LOGIN'),
                getenv('MYSQL_PASSWORD'),
                getenv('MYSQL_DATABASE'),
                (int)getenv('MYSQL_PORT'),
            ]
        );

        $userRep = new \Symfony\Component\DependencyInjection\Definition(
            \Afup\Tombola\UserRepository::class,
            [new \Symfony\Component\DependencyInjection\Reference('app.mysqli')]
        );


        $c
            ->loadFromExtension('framework', [
                'secret' => 'micr0',
                'session' => [
                    'handler_id' => 'session.handler.native_file',
                    'save_path' => "%kernel.root_dir%/sessions",
                ],
                'templating' => ['engines' => ['twig']],
            ])
            ->setDefinition('app.mysqli', $mysqli)
        ;
        $c
            ->setDefinition('app.user_repository', $userRep)
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
        $adminUsers = explode(',', getenv('AFUP_TOMBOLA_ADMIN_USERS'));

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
            $user = $this->getAuth()->getResourceOwner($token);
            $data = $user->toArray();

            $admin = in_array($user->getNickname(), $adminUsers);

            $userData = [
                'provider' => 'github',
                'id' => $user->getId(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'email' => $user->getEmail(),
                'avatar' => isset($data['avatar_url']) ? $data['avatar_url'] : null,
                'admin' => $admin,
            ];

            $userData['access-token'] = $token->getToken();
            $userData['refresh-token'] = $token->getRefreshToken();

            $session->set('user', $userData);
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        $this->getContainer()->get('app.user_repository')->insertUser($userData);

        $redirect = '/tombola';
        if ($admin) {
            $redirect = '/admin';
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse($redirect);
    }

    public function adminAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $user = $request->getSession()->get('user');

        if (false === $user['admin']) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $users = $this->getContainer()->get('app.user_repository')->getUsers();

        return new \Symfony\Component\HttpFoundation\Response(
            $this->getContainer()->get('templating')->render(
                dirname(__FILE__) . '/templates/admin.html.twig',
                [
                    'users' => $users
                ]
            )
        );
    }

    public function tombolaAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        if ($request->getSession()->get('user') === null) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse('/login');
        }
        return new \Symfony\Component\HttpFoundation\Response(
            $this->getContainer()->get('templating')->render(
                dirname(__FILE__) . '/templates/tombola.html.twig'
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
