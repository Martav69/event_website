<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // on récupère ici tout les evenements
        $queryBuilder = $eventRepository->createQueryBuilder('e');

        //mise en place de la pagination ici
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), 
            6 // Nombre d'éléments par page
        );

        return $this->render('event/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $picFile */
            $picFile = $form->get('picFile')->getData();

            if ($picFile) {
                $originalFilename = pathinfo($picFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $filename = $safeFilename . '-' . uniqid() . '.' . $picFile->guessExtension();

                try {
                    $picFile->move(
                        $this->getParameter('uploads_event_directory'),
                        $filename
                    );

                    $event->setPicFileName($filename);
                    
                } catch (FileException $e) {
                    $form->addError(new FormError("Erreur lors de l'upload du fichier"));
                }
            }

            // Gestion de la ville et du pays
            $country = $form->get('country')->getData();
            $cityName = $form->get('cityName')->getData();

            // Vérification si la ville existe déjà pour ce pays
            $city = $em->getRepository(City::class)->findOneBy([
                'name' => $cityName,
                'country' => $country,
            ]);

            // Si la ville n'existe pas, crée-la
            if (!$city) {
                $city = new City();
                $city->setName($cityName);
                $city->setCountry($country);
                $em->persist($city);
                $em->flush();
            }

            // Associe la ville à l'événement
            $event->addCity($city);

            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // On récupère la première ville lié à l'event
        $city = $event->getCities()->first();
        
        // On crée le formulaire et pré-remplis les données manuellement car unmapped sur le formulaire pour crée de nouvelles entité si elles n'existe pas 
        $form = $this->createForm(EventType::class, $event);
        $form->get('country')->setData($city ? $city->getCountry() : null);
        $form->get('cityName')->setData($city ? $city->getName() : '');
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $picFile */
            $picFile = $form->get('picFile')->getData();
    
            if ($picFile) {
                // Supprime l'ancienne image si elle existe
                if ($event->getPicFileName()) {
                    $oldFile = $this->getParameter('uploads_event_directory').'/'.$event->getPicFileName();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
    
                $originalFilename = pathinfo($picFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $filename = $safeFilename . '-' . uniqid() . '.' . $picFile->guessExtension();
    
                try {
                    $picFile->move(
                        $this->getParameter('uploads_event_directory'),
                        $filename
                    );
    
                    $event->setPicFileName($filename);
                    
                } catch (FileException $e) {
                    $form->addError(new FormError("Erreur lors de l'upload du fichier"));
                }
            }
    
            // Gestion de la ville et du pays
            $country = $form->get('country')->getData();
            $cityName = $form->get('cityName')->getData();
    
            // Vérification si la ville existe déjà pour ce pays
            $city = $em->getRepository(City::class)->findOneBy([
                'name' => $cityName,
                'country' => $country,
            ]);
    
            // Si la ville n'existe pas, on la crée
            if (!$city) {
                $city = new City();
                $city->setName($cityName);
                $city->setCountry($country);
                $em->persist($city);
                $em->flush();
            }

            // Supprimer toutes les villes existantes liées à l'événement
            foreach ($event->getCities() as $existingCity) {
            $event->removeCity($existingCity);
        }
    
            // Associe la ville à l'événement
            $event->addCity($city);
    
            $em->flush();
    
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->get('_token'))) {
            $em->remove($event);
            $em->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
