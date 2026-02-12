<?php
namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controleur de l'accueil
 *
 * @author emds
 */
class AccueilController extends AbstractController{
      
    /**
     * @var FormationRepository
     */
    private $repository;
    
    /**
     * 
     * @param FormationRepository $repository
     */
    public function __construct(FormationRepository $repository) {
        $this->repository = $repository;
    }   
    
    /**
 * @Route("/", name="accueil")
 */
public function index(): Response {
    // SI l'utilisateur est connecté (après un login réussi), on l'envoie vers l'admin
    if ($this->getUser()) {
        return $this->redirectToRoute('admin.formations');
    }

    // SINON (après un logout ou première visite), on affiche simplement la vue
    // SANS REDIRIGER vers Keycloak.
    $formations = $this->repository->findAllLasted(2);
    return $this->render("pages/accueil.html.twig", [
        'formations' => $formations
    ]); 
}

    
    /**
     * @Route("/cgu", name="cgu")
     * @return Response
     */
    public function cgu(): Response{
        return $this->render("pages/cgu.html.twig"); 
    }
}
