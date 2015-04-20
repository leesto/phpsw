<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse;

class MessageController extends AbstractController
{
    public function sendAction(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;

        $referer = $request->headers->get('referer');
        $subject = $request->get('subject');

        if ($request->get('name')) {
            $response = $this->fail(); # bot
        } elseif (!trim($request->get('message'))) {
            $response = $this->fail('Please enter a message');
        } elseif (strpos($referer, 'http://' . $request->getHttpHost() . '/') !== 0) {
            $response = $this->fail('Invalid referrer');
        } else {
            $message = $request->get('message');
            $abstract = substr(trim(preg_replace('#\s+#', ' ', $message)), 0, 60);

            $data = (object) [
                'subject'  => $subject,
                'message'  => $message,
                'referer'  => $referer,
                'datetime' => date('c')
            ];

            $app['redis']->lpush("messages", json_encode($data, JSON_PRETTY_PRINT));

            $email = $app['mailer']->createMessage()
                ->setSubject("PHPSW {$subject}: {$abstract}…")
                ->setFrom($app['email'])
                ->setTo($app['email'])
                ->setBody("{$message}


--

Referrer: {$referer}")
            ;

            if ($app->mail($email)) {
                if ($subject != 'complaint') {
                    $response = $this->success("Thank you for your {$subject}!");
                } else {
                    $response = $this->success('Thank you, your report has been submitted, we will look into it and take action accordingly');
                }
            } else {
                $response = $this->fail("Something went wrong, try again?");
            }
        }

        return $response;
    }

    private function success($message)
    {
        $uri = (object) parse_url($this->request->headers->get('referer'));

        return new RedirectResponse(sprintf(
            '%s://%s%s?%s#%s',
            $uri->scheme,
            $uri->host,
            $uri->path,
            http_build_query(['success' => $message]),
            $this->request->get('subject')
        ));
    }

    private function fail($message = null)
    {
        $uri = (object) parse_url($this->request->headers->get('referer'));

        return new RedirectResponse(sprintf(
            '%s://%s%s?%s#%s',
            $uri->scheme,
            $uri->host,
            $uri->path,
            http_build_query(array_filter([
                'message' => $this->request->get('message'),
                'fail' => $message
            ])),
            $this->request->get('subject')
        ));
    }
}
