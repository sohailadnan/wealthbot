<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Controller\ChangePasswordController as BaseController;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wealthbot\RiaBundle\Mailer\MailerInterface;

/**
 * Controller managing the password change.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ChangePasswordController extends BaseController
{
    public function resetPasswordAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var $mailer MailerInterface */
        $mailer = $this->container->get('wealthbot_ria.mailer');
        $user = $em->getRepository('WealthbotUserBundle:User')->find($request->get('user_id'));

        if (!$user) {
            throw $this->createNotFoundException('User does not exist.');
        }

        $user->setPlainPassword($user->generateTemporaryPassword());
        $mailer->sendConfirmationEmailMessage($user);

        $this->container->get('session')->getFlashBag()->add('success', 'Password for user "'.$user->getProfile()->getLastName().' '.$user->getProfile()->getFirstName().'" has been reseted successfully.');

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * Generate the redirection url when the resetting is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('rx_after_login');
    }

    protected function setFlash($action, $value)
    {
        $this->container->get('session')-> getFlashBag()->add($action, $value);
    }
}
