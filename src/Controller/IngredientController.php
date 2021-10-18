<?php

namespace App\Controller;
//header('Access-Control-Allow-Origin: *');

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use PhpParser\Node\Expr\Cast\Object_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
class IngredientController extends AbstractController
{
    private $ingredientRepository;
    public function __construct(IngredientRepository $ingredientRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('ingredient/index.html.twig', [
            'controller_name' => 'IngredientController',
        ]);
    }
    /**
     * @Route("/ingredients", name="ajouter-ingredient",methods={"POST"})
     */
    public function addIngredient(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ingredient = new Ingredient();

        $nom = $data['nom'];

        if (empty( $nom )) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $ingredient->setNom($nom);

        $this->ingredientRepository->saveIngredient($ingredient);
        return new JsonResponse(['status' => 'Ingredient created!'], Response::HTTP_CREATED);
    }
    /**
     * @Route("/ingredients", name="Get_All_ingredients", methods={"GET"})
     */

    public function AllIngredient(Request $request): JsonResponse
    {
        $ingredients = $this->getDoctrine()->getRepository(Ingredient::class)->findAll();
        $data = [];

        foreach ($ingredients as $ingredient) {
            $data[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getNom(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
    /**
     * @Route("/ingredients/{id}", name="update_ingredients", methods={"PUT"})
     */

    public function UpdateIngredient($id, Request $request): JsonResponse
    {
        $ingredients = $this->ingredientRepository->findOneBy(['id'=> $id]);
        $data = json_decode($request->getContent(), true);

        empty($data['nom']) ? true : $ingredients->setNom($data['nom']);
        $updatedIngredient = $this->ingredientRepository->updateIngredient($ingredients);
        return new JsonResponse($updatedIngredient, Response::HTTP_OK);
    }
    /**
     * @Route("/ingredient/{id}", name="Get_By_ingredients", methods={"GET"})
     */

    public function findById($id): JsonResponse
    {
        $ingredient = $this->getDoctrine()->getRepository(Ingredient::class)->findOneBy(['id'=> $id]);

        return new JsonResponse($ingredient, Response::HTTP_OK);
    }
    /**
     * @Route("/ingredients/{id}", name="delete_ingredient", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {

        $ingredient = $this->ingredientRepository->findOneBy(['id' => $id]);

        $this->ingredientRepository->removeIngredient($ingredient);

        return new JsonResponse(['status' => 'Ingredient deleted'], Response::HTTP_NO_CONTENT);
    }
}
