<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function create(Request $request,ObjectManager $manager)
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
     */
    public function list(TaskRepository $repo, $isDone = false)
    {
        $template = false === $isDone ? 'task/list.html.twig' : 'task/listCompleted.html.twig';

        return $this->render(
            $template, [
            'tasks' => $repo->findBy([
                'user' => $this->getUser(),
                'isDone' => $isDone
            ])
        ]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function edit(Task $task, Request $request, ObjectManager $manager)
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'message.task.update.success');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTask(Task $task, ObjectManager $manager, TranslatorInterface $translator)
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
     */
    public function deleteTask(Task $task, ObjectManager $manager)
    {
        $manager->remove($task);
        $manager->flush();

        $this->addFlash('success', 'message.task.delete.success');

        return $this->redirectToRoute('task_list');
    }
}
