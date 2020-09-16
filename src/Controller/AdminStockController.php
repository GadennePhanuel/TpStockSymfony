<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Belonging;
use App\Entity\Stock;
use App\Form\ArticleQtyType;
use App\Form\ArticleType;
use App\Form\BelongingType;
use App\Form\StockType;
use App\Repository\ArticleRepository;
use App\Repository\BelongingRepository;
use App\Repository\StockRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminStockController extends AbstractController
{

    /**
     * @var BelongingRepository
     */
    private $belongingRepository;
    /**
     * @var StockRepository
     */
    private $stockRepository;
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    public function __construct(BelongingRepository $belongingRepository, StockRepository $stockRepository, ArticleRepository $articleRepository, ObjectManager $manager)
    {

        $this->belongingRepository = $belongingRepository;
        $this->stockRepository = $stockRepository;
        $this->articleRepository = $articleRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/admin", name="admin.stock.index")
     * @return Response
     */
    public function index(): Response
    {
        $stocks = $this->stockRepository->findAll();

        return $this->render('admin/stock/index.html.twig', [
            'controller_name' => 'AdminStockController',
            'stocks' => $stocks
        ]);
    }


    /**
     * @Route("/admin/stock/create", name="admin.stock.new")
     * @param Request $request
     * @return Response
     */
    public function newStock(Request $request): Response
    {
        $stock = new Stock();
        $formStock = $this->createForm(StockType::class, $stock);
        $formStock->handleRequest($request);

        if ($formStock->isSubmitted() && $formStock->isValid()){
            $this->manager->persist($stock);
            $this->manager->flush();

            return $this->redirectToRoute('admin.stock.index');
        }

        return $this->render('admin/stock/new.html.twig', [
            'article' => $stock,
            'formStock' => $formStock->createView()
        ]);
    }


    /**
     * @Route("/admin/stock/search", name="admin.stock.article.search")
     * @return Response
     */
    public function search(): Response
    {

        return $this->render('admin/stock/article/search.html.twig', [

        ]);
    }


    /**
     * @Route("/admin/stock/search/{ref}", name="admin.stock.article.search.ref")
     * @param $ref
     * @return JsonResponse
     */
    public function resultSearch($ref)
    {
        $article = $this->articleRepository->findOneBy(['ref' => $ref]);


        if ($article == null){
            return $this->json(['search' => false, 'message' => 'pas d\'article correspondant à cette référence'], 200);
        }else{
            $articleId = $article->getId();
            $belong = $this->belongingRepository->findBy(['article' => $articleId]);
            return $this->json(['search' => true ,'message' => 'article trouvé', 'article' => $article, 'belong' => $belong], 200);
        }


    }



    /**
     * @Route("admin/stock/{id}/add", name="admin.stock.inspect.addArticle")
     * @param Stock $stock
     * @param $id
     * @return Response
     */
    public function addArticle($id, Stock $stock): Response
    {
        $articles = $this->articleRepository->findAllArticleNotInCurrentStock($id);

        return $this->render('admin/stock/add.html.twig', [
            'articles' => $articles,
            'stock' => $stock,
        ]);
    }


    /**
     * @Route("admin/stock/{idStock}/add/{idArticle}", name="admin.stock.inspect.addArticle.current")
     * @param $idStock
     * @param $idArticle
     * @param Request $request
     * @return Response
     */
    public function addArticleAtCurrentStock($idStock, $idArticle, Request $request): Response
    {
        $stock = $this->stockRepository->find($idStock);
        $article = $this->articleRepository->find($idArticle);

        $belong = new Belonging();
        $belong->setArticle($article);
        $belong->setStock($stock);

        $formBelong = $this->createForm(BelongingType::class, $belong);
        $formBelong->handleRequest($request);

        if ($formBelong->isSubmitted() && $formBelong->isValid()){
            $this->manager->persist($belong);
            $this->manager->flush();

            return $this->redirectToRoute('admin.stock.inspect.addArticle', array('id' => $idStock));
        }

        return $this->render('admin/stock/addForm.html.twig', [
            'stock' => $stock,
            'article' => $article,
            'formBelong' => $formBelong->createView()
        ]);
    }



    /**
     * @Route("/admin/stock/{id}", name="admin.stock.inspect")
     * @param Stock $stock
     * @param $id
     * @return Response
     */
    public function inspectStock(Stock $stock, $id): Response
    {
        //j'ai besoin de récupérer l'état du stock qui correspond à l'id du stock donnée
        $belongs = $this->belongingRepository->findBy(array('stock' => $id));

        return $this->render('admin/stock/inspect.html.twig', [
            'stock' => $stock,
            'belongs' => $belongs
        ]);
    }



    /**
     * @Route("/admin/stock/article/create", name="admin.stock.article.new")
     * @param Request $request
     * @return Response
     */
    public function newArticle(Request $request): Response
    {
        $article = new Article();
        $formArticle = $this->createForm(ArticleType::class, $article);
        $formArticle->handleRequest($request);

        if ($formArticle->isSubmitted() && $formArticle->isValid()){
            $this->manager->persist($article);
            $this->manager->flush();

            return $this->redirectToRoute('admin.stock.article.index');
        }

        return $this->render('admin/stock/article/new.html.twig', [
            'article' => $article,
            'formArticle' => $formArticle->createView()
        ]);
    }


    /**
     * @Route("/admin/stock/article/index", name="admin.stock.article.index")
     * @return Response
     */
    public function showArticle(): Response
    {
        $articles = $this->articleRepository->findAll();
        return $this->render('admin/stock/article/index.html.twig', [
            'articles' => $articles
        ]);
    }



    /**
     * @Route("/admin/stock/article/editArticle/{id_article}", name="admin.stock.article.editArticle")
     * @param Request $request
     * @param $id_article
     * @return Response
     */
    public function editArticle(Request $request, $id_article): Response
    {
        $article = $this->articleRepository->find($id_article);
        $formArticle = $this->createForm(ArticleType::class, $article);
        $formArticle->handleRequest($request);

        if ($formArticle->isSubmitted() && $formArticle->isValid()){
            $this->manager->flush();
            return $this->redirectToRoute('admin.stock.article.index');
        }



        return $this->render('admin/stock/article/editArticle.html.twig', [
            'article' => $article,
            'formArticle' => $formArticle->createView()
        ]);
    }





    /**
     * @Route("/admin/stock/{id}/{id_belong}", name="admin.stock.article.editQty" )
     * @param Stock $stock
     * @param $id
     * @param $id_belong
     * @param Request $request
     * @return Response
     */
    public function editQtyArticle(Stock $stock, $id, $id_belong, Request $request): Response
    {
        $belong = $this->belongingRepository->find($id_belong);

        $formQty = $this->createForm(ArticleQtyType::class, $belong);
        $formQty->handleRequest($request);

        if ($formQty->isSubmitted() && $formQty->isValid()){
            $this->manager->flush();
            return $this->redirectToRoute('admin.stock.inspect', array('id' => $id));
        }


        return $this->render('admin/stock/article/editQty.html.twig', [
            'stock' => $stock,
            'belong' => $belong,
            'formQty' => $formQty->createView()
        ]);
    }

}

















