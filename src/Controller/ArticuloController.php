<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Articulo;
use App\Entity\Categoria;

class ArticuloController extends AbstractController
{
    /**
     * @Route("/", name="inicio")
     */
    public function index(): Response
    {
        $repositorio=$this->getDoctrine()->getRepository(Articulo::class);
        $articulos=$repositorio->findAll();
        return $this->render('articulo/index.html.twig', ['articulos' => $articulos]);
    }
    /**
     * @Route("/articulo/{id}", name="ver_articulo", requirements={"id"="\d+"})
     * @param int $id
     */
    public function ver(Articulo $articulo){
        /*$repositorio=$this->getDoctrine()->getRepository(Articulo::class);
         * $articulo = $repositorio->find($id);
        */
        return $this->render('articulo/ver_articulo.html.twig', ['articulo' => $articulo]);
    }
    /**
     * @Route("/articulo/insertar", name="insertar_articulo")
     */
    public function insertar(): Response{
        $a = new Articulo();
        $a->setNombre('Galaxy s5');
        $a->setDescripcion('Descripcion DescripcionDescripcionDescripcionDescripcionDescripcionDescripcion');
        $a->setPrecio(200);
        
        $categoria = $this->getDoctrine()->getRepository(Categoria::class)->findOneBy(['nombre'=>'Moviles']);
        $a->setCategoria($categoria);
        
        $em=$this->getDoctrine()->getManager();
        $em->persist($a);
        $em->flush();
        
        return $this->redirectToRoute('inicio');
    }
    /**
     * @Route("/articulo/borrar/{id}", name="borrar_articulo",requirements={"id"="\d+"})
     */
    public function borrar(Articulo $articulo){
        $em=$this->getDoctrine()->getManager();
        $em->remove($articulo);
        $em->flush();
        
        return $this->redirectToRoute('inicio');
    }
}
