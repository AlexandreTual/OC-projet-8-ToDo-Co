<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordType;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_list")
     *
     * @param UserRepository $repo
     *
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(UserRepository $repo): Response
    {
        return $this->render('user/list.html.twig', ['users' => $repo->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     *
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param ObjectManager                $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $manager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'message.user.add.success');

            return $this->redirectToRoute('login');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     *
     * @param User          $user
     * @param Request       $request
     * @param ObjectManager $manager
     *
     * @Security("is_granted('ROLE_ADMIN') or (user.GetId() === userForEdit.getId())")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(User $userForEdit, Request $request, ObjectManager $manager): Response
    {
        $form = $this->createForm(UserEditType::class, $userForEdit);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'message.user.update.success');

            /** @codeCoverageIgnore */
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $userForEdit]);
    }

    /**
     * @Route("/users/{id}/edit/password", name="edit_password")
     *
     * @param User                         $user
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     * @param ObjectManager                $manager
     *
     * @Security("is_granted('ROLE_ADMIN') or (user.GetId() === userForEdit.getId())")
     *
     * @codeCoverageIgnore
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editPassword(User $userForEdit, Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $manager): Response
    {
        $form = $this->createForm(EditPasswordType::class, $userForEdit);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($userForEdit, $userForEdit->getPassword());
            $userForEdit->setPassword($password);
            $manager->flush();
            $this->addFlash('success', 'message.user.edit.password.success');

            return $this->redirectToRoute('user_list');
        }

        return $this->render(
            'user/editPassword.html.twig',
            [
                'form' => $form->createView(),
                'user' => $userForEdit,
            ]
        );
    }

    /**
     * @Route("/user/{id}/role/{role}", name="edit_role")
     * @ParamConverter("user", options={"mapping": {"id": "id"}})
     *
     * @param $role
     * @param User                $user
     * @param ObjectManager       $manager
     * @param TranslatorInterface $translator
     *
     * @codeCoverageIgnore
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editRole($role, User $user, ObjectManager $manager, TranslatorInterface $translator): RedirectResponse
    {
        $roles[] = $role;
        $user->setRoles($roles);
        $manager->flush();
        $this->addFlash('success', $translator->trans(
            'message.user.edit.role.success',
            [
                'user' => $user->getUsername(),
                'role' => 'ROLE_USER' === $user->getRoles()[0] ? $translator->trans('word.user') : $translator->trans('word.admin'),
            ]
        ));

        return $this->redirectToRoute('user_list');
    }
}
