<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Response;

class SpeakerController extends AbstractController
{
    public function indexAction(Application $app)
    {
        return $this->render($app, 'speakers.html.twig', [
            'speakers' => $app['meetup.client']->getSpeakers()
        ]);
    }

    public function showAction(Application $app, $slug)
    {
        $speaker = $app['meetup.client']->getSpeaker($slug);

        if (!$speaker) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->render($app, 'speaker.html.twig', [
            'speaker' => $speaker
        ]);
    }

    public function photoAction(Application $app, $slug, $size)
    {
        $speaker = $app['meetup.client']->getSpeaker($slug);

        if (!$speaker) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        $photo = $speaker->photo;

        switch ($size) {
            case 'highres':
                if (isset($photo->highres_link)) {
                    $size = 'highres';
                    break;
                }
            case 'photo':
                if (isset($photo->photo_link)) {
                    $size = 'photo';
                    break;
                }
            default:
                $size = 'thumb';
        }

        try {
            $response = $app['guzzle']->get($photo->{"${size}_link"});
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $app->abort($e->getResponse()->getStatusCode());
        }

        return new Response($response->getBody(), $response->getStatusCode(), [
            'Cache-Control' => 'public',
            'Content-Type' => (string) $response->getHeader('Content-Type'),
            'Expires' => (new \DateTime('+2 weeks'))->format('D, d M Y H:i:s T')
        ]);
    }
}
