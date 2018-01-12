<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\TwitterSchedule;
use AppBundle\Form\Type\TwitterScheduleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class TwitterScheduleController extends AbstractController
{
    public function listAction(Request $request, UserInterface $user, string $citySlug): Response
    {
        $city = $this->getDoctrine()->getRepository(City::class)->findOneBySlug($citySlug);

        if (!$city) {
            throw $this->createNotFoundException();
        }

        $scheduleList = $this->getDoctrine()->getRepository(TwitterSchedule::class)->findByCity($city);

        return $this->render(
            'AppBundle:TwitterSchedule:list.html.twig', [
                'scheduleList' => $scheduleList,
                'city' => $city,
            ]
        );
    }

    public function editAction(Request $request, UserInterface $user, string $citySlug): Response
    {
        $city = $this->getDoctrine()->getRepository(City::class)->findOneBySlug($citySlug);

        if (!$city) {
            throw $this->createNotFoundException();
        }

        $scheduleId = $request->query->getInt('scheduleId');
        $schedule = $this->getDoctrine()->getRepository(TwitterSchedule::class)->find($scheduleId);

        if (!$schedule) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TwitterScheduleType::class, $schedule, [
            'city' => $city,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schedule = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('twitter_schedule_list', ['citySlug' => $city->getSlug()]);
        }

        return $this->render(
            'AppBundle:TwitterSchedule:edit.html.twig', [
                'scheduleForm' => $form->createView(),
            ]
        );
    }

    public function addAction(Request $request, UserInterface $user, string $citySlug): Response
    {
        $city = $this->getDoctrine()->getRepository(City::class)->findOneBySlug($citySlug);

        if (!$city) {
            throw $this->createNotFoundException();
        }

        $schedule = new TwitterSchedule();

        $form = $this->createForm(TwitterScheduleType::class, $schedule, [
            'city' => $city,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schedule = $form->getData();

            $schedule->setCity($city);

            $em = $this->getDoctrine()->getManager();
            $em->persist($schedule);
            $em->flush();

            return $this->redirectToRoute('twitter_schedule_list', ['citySlug' => $city->getSlug()]);
        }

        return $this->render(
            'AppBundle:TwitterSchedule:edit.html.twig', [
                'scheduleForm' => $form->createView(),
            ]
        );
    }
}
