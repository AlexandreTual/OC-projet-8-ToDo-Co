<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/create", name="task_create")
     *
     * @param Request       $request
     * @param ObjectManager $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, ObjectManager $manager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $manager->persist($task);
            $manager->flush();

            $this->addFlash('success', 'message.task.add.success');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{isDone}", name="task_list", requirements={"isDone": "1|0"})
     *
     * @param TaskRepository $repo
     * @param bool           $isDone
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(TaskRepository $repo, $isDone = false): Response
    {
        $template = false === $isDone ? 'task/list.html.twig' : 'task/listCompleted.html.twig';

        $tasks = 'ROLE_ADMIN' === $this->getUser()->getRoles()[0] ? $repo->findAll() : $repo->findBy(['user' => $this->getUser(), 'isDone' => $isDone]);

        return $this->render(
            $template,
            [
                'tasks' => $tasks,
                'user' => $this->getUser(),
            ]
        );
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     *
     * @param Task          $task
     * @param Request       $request
     * @param ObjectManager $manager
     *
     * @Security("is_granted('ROLE_USER') and user.getId() === task.getUser().getId()")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Task $task, Request $request, ObjectManager $manager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'message.task.update.success');

            return $this->redirectToRoute('task_list');
        }

        return $this->render(
            'task/edit.html.twig',
            [
                'form' => $form->createView(),
                'task' => $task,
            ]
        );
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     *
     * @param Task                $task
     * @param ObjectManager       $manager
     * @param TranslatorInterface $translator
     *
     * @Security("is_granted('ROLE_USER') and user.getId() === task.getUser().getId()")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleTask(Task $task, ObjectManager $manager, TranslatorInterface $translator): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $manager->flush();

        $this->addFlash(
            $task->isDone() ? 'success' : 'warning',
            $translator->trans(
                $task->isDone() ? 'message.task.completed.success' : 'message.task.to.complete.success',
                ['title' => $task->getTitle()]
            )
        );

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     *
     * @param Task          $task
     * @param ObjectManager $manager
     *
     * @Security("(is_granted('ROLE_USER') and user.getId() === task.getUser().getId()) or (is_granted('ROLE_ADMIN') and task.getUser().getRoles()[0] === 'ROLE_ANONYMOUS')")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteTask(Task $task, ObjectManager $manager): RedirectResponse
    {
        $manager->remove($task);
        $manager->flush();

        $this->addFlash('success', 'message.task.delete.success');

        return $this->redirectToRoute('task_list');
    }
}
