<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminFormationController extends AbstractController
{
    private $formationRepository;
    private $categorieRepository;
    private $entityManager;
    
    public function __construct(
        FormationRepository $formationRepository,
        CategorieRepository $categorieRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
        $this->entityManager = $entityManager;
    }
    
    #[Route('/admin/formation', name: 'app_admin_formation')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        
        return $this->render('admin_formation/index.html.twig', [
            'formations' => $formations,
            'categories' => $categories,
        ]);
    }
    
    #[Route('/admin', name: 'admin.formations')]
    public function dashboard(): Response
    {
        return $this->render('baseadmin.html.twig');
    }
    
    #[Route('/admin/formation/ajout', name: 'admin.formation.ajout')]
    public function ajout(Request $request): Response
    {
        $formation = new Formation();
        $formFormation = $this->createForm(FormationType::class, $formation);
        
        $formFormation->handleRequest($request);
        
        if ($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->entityManager->persist($formation);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La formation a été ajoutée avec succès.');
            return $this->redirectToRoute('app_admin_formation');
        }
        
        return $this->render('admin_formation/ajout.html.twig', [
            'formation' => $formation,
            'formFormation' => $formFormation->createView(),
        ]);
    }
    
    #[Route('/admin/formation/edit/{id}', name: 'admin.formation.edit')]
    public function edit(Request $request, int $id): Response
    {
        $formation = $this->formationRepository->find($id);
        
        if (!$formation) {
            $this->addFlash('alert', 'Formation non trouvée.');
            return $this->redirectToRoute('app_admin_formation');
        }
        
        $formFormation = $this->createForm(FormationType::class, $formation);
        
        $formFormation->handleRequest($request);
        
        if ($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La formation a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_formation');
        }
        
        return $this->render('admin_formation/edit.html.twig', [
            'formation' => $formation,
            'formFormation' => $formFormation->createView(),
        ]);
    }
    
    #[Route('/admin/formation/suppr/{id}', name: 'admin.formation.suppr')]
    public function suppr(int $id): Response
    {
        $formation = $this->formationRepository->find($id);
        
        if ($formation) {
            $this->entityManager->remove($formation);
            $this->entityManager->flush();
            $this->addFlash('success', 'La formation a été supprimée avec succès.');
        } else {
            $this->addFlash('alert', 'Formation non trouvée.');
        }
        
        return $this->redirectToRoute('app_admin_formation');
    }
    
    #[Route('/admin/formation/sort/{champ}/{ordre}/{table?}', name: 'admin.formations.sort')]
    public function sort(string $champ, string $ordre, ?string $table = null): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        
        return $this->render('admin_formation/index.html.twig', [
            'formations' => $formations,
            'categories' => $categories,
        ]);
    }
    
    #[Route('/admin/formation/findallcontain/{champ}/{table?}', name: 'admin.formations.findallcontain', methods: ['POST'])]
public function findAllContain(Request $request, string $champ, ?string $table = null): Response
{
    $valeur = $request->request->get('recherche');
    
    // Appel à la méthode existante avec les bons paramètres
    $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
    
    $categories = $this->categorieRepository->findAll();
    
    return $this->render('admin_formation/index.html.twig', [
        'formations' => $formations,
        'categories' => $categories,
        'valeur' => $valeur,
        'table' => $table,
    ]);
}
}