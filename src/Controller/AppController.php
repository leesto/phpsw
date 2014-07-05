<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class AppController
{
    public function indexAction(Request $request, Application $app)
    {
        return new Response($app['twig']->render('index.html.twig', ['meetup' => $app['meetup.client']]), 200, [
            'Cache-Control' => 's-maxage=3600'
        ]);
    }

    public function speakersAction(Request $request, Application $app)
    {
        return new Response($app['twig']->render('speakers.html.twig', ['meetup' => $app['meetup.client']]), 200, [
            'Cache-Control' => 's-maxage=3600'
        ]);
    }

    public function sponsorsAction(Request $request, Application $app)
    {
        return new Response($app['twig']->render('sponsors.html.twig', ['meetup' => $app['meetup.client']]), 200, [
            'Cache-Control' => 's-maxage=3600'
        ]);
    }
}
