<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


class RecetteController extends AbstractController
{


    private $recetteRepository;
    public function __construct(RecetteRepository $recetteRepository)
    {
        $this->recetteRepository = $recetteRepository;
    }

    /**
     * @Route("/", name="recette")
     */
    public function index(): Response
    {
        return $this->render('recette/index.html.twig', [
            'controller_name' => 'RecetteController',
        ]);
    }
    /**
         * @Route("/recettes", name="ajouter-recette", methods={"POST"})
     */
    public function addRecette(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $recette = new Recette();

        $titre = $data['titre'];
        $sous_titre = $data['sous_titre'];
        $ingredients = $data['ingredients'];
        if (empty( $titre )||empty( $sous_titre )||empty( $ingredients ) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $recette->setTitre($titre);
        $recette->setSousTitre($sous_titre);

       foreach ($ingredients as $index){
           $ingredient = new Ingredient();
           $data = $this->getDoctrine()->getRepository(Ingredient::class)->findOneBy(['id'=>$index['id']]);
           $ingredient->setId($data->getId());
           $ingredient->setNom($data->getNom());
           $recette->addIngredient($ingredient);
        }
       $this->recetteRepository->saveRecette($recette);

        return new JsonResponse(['status' => 'Recette created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/recettes/{id}", name="update_recettes", methods={"PUT"})
     */

    public function UpdateRecette($id, Request $request): JsonResponse
    {
        $recette = $this->recetteRepository->findOneBy(['id'=> $id]);

        $data = json_decode($request->getContent(), true);

        $titre = $data['titre'];
        $sous_titre = $data['sous_titre'];
        $ingredients = $data['ingredients'];

        if (empty( $titre )||empty( $sous_titre )||empty( $ingredients ) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $recette->setTitre($titre);
        $recette->setSousTitre($sous_titre);
        $recette->getIngredients()->clear();
        foreach ($ingredients as $index){
            $data = $this->getDoctrine()->getRepository(Ingredient::class)->findOneBy(['id'=>$index['id']]);
            $recette->addIngredient($data);
        }

        $updatedRecette = $this->recetteRepository->updateRecette($recette);
        return new JsonResponse($updatedRecette, Response::HTTP_OK);
    }

    /**
     * @Route("/recettes", name="recette", methods={"GET"})
     */

    public function AllRecette(Request $request): JsonResponse
    {
        $recettes = $this->getDoctrine()->getRepository(Recette::class)->findAll();
        $data = [];

        foreach ($recettes as $recette) {
            $dataIngrediant = [];
            foreach ($recette->getIngredients() as $ingrediant) {
                $dataIngrediant[] = ['id'=> $ingrediant->getId(),'nom'=> $ingrediant->getNom()];
            }
                $data[] = [
                'id' => $recette->getId(),
                'titre' => $recette->getTitre(),
                'sous_titre' => $recette->getSousTitre(),
                'ingredients' => $dataIngrediant,
            ];

        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
    /**
     * @Route("/recettes/{id}", name="delete_recettes", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {

        $recette = $this->recetteRepository->findOneBy(['id' => $id]);

        $this->recetteRepository->removeRecette($recette);

        return new JsonResponse(['status' => 'recette deleted'], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/recette/{id}", name="Get_By_recette", methods={"GET"})
     */

    public function findById($id): JsonResponse
    {
        $recette = $this->getDoctrine()->getRepository(Recette::class)->findOneBy(['id'=> $id]);


        foreach ($recette->getIngredients() as $ingrediant) {
            $dataIngrediant[] = ['id'=> $ingrediant->getId(),'nom'=> $ingrediant->getNom()];
        }


        $data = (object) array(
            'id' => $recette->getId(),
            'titre' => $recette->getTitre(),
            'sous_titre' => $recette->getSousTitre(),
            'ingredients' => $dataIngrediant,
        );
        return new JsonResponse($data, Response::HTTP_OK);
    }
}
