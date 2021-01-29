<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Articulo;
use App\Entity\Categoria;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class ArticuloController extends AbstractController
{
    /**
     * @Route("/", name="inicio")
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $repositorio=$this->getDoctrine()->getRepository(Articulo::class);
        $articulos=$repositorio->findAll();
        return $this->render('articulo/index.html.twig', ['articulos' => $articulos ,'last_username' => $lastUsername, 'error' => $error]);
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
     * @IsGranted("ROLE_USER")
     */
    public function insertar(Request $request, SluggerInterface $slugger): Response{
        $articulo = new Articulo(); 
        $form=$this->createFormBuilder($articulo)
                ->add('nombre', TextType::class)
                ->add('descripcion', TextareaType::class)
                ->add('precio', MoneyType::class)
                ->add('categoria', EntityType::class, ['class'=> Categoria::class, 'choice_label'=>'nombre'])
                /**
                *
                 * 'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ]
                 *                  
                 */
                ->add('enviar', SubmitType::class, ['label'=>'Insertar articulo'])
                ->getForm();
        
        $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
            $imagen = $form->get('foto')->getData();
            if ($imagen) {
                $originalFilename = pathinfo($imagen->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imagen->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imagen->move(
                        $this->getParameter('rutaSubidaImagen'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $articulo = $form->getData();
                $articulo->setFoto($newFilename);
            }        
            //guardamos el nuevo articulo en la base de datos
            $em=$this->getDoctrine()->getManager();
            $em->persist($articulo);
            $em->flush();
            return $this->redirectToRoute('inicio');
        }
        
        
        return $this->render('articulo/insertar_articulo.html.twig', ['formulario'=>$form->createView()]);
       
        /*$a->setNombre('Galaxy s5');
        $a->setDescripcion('Descripcion DescripcionDescripcionDescripcionDescripcionDescripcionDescripcion');
        $a->setPrecio(200);
        
        $categoria = $this->getDoctrine()->getRepository(Categoria::class)->findOneBy(['nombre'=>'Moviles']);
        $a->setCategoria($categoria);
        
        $em=$this->getDoctrine()->getManager();
        $em->persist($a);
        $em->flush();
        
        return $this->redirectToRoute('inicio');*/
    }
    /**
     * @Route("/articulo/borrar/{id}", name="borrar_articulo",requirements={"id"="\d+"})
     */
    public function borrar(Articulo $articulo): Response{
        $em=$this->getDoctrine()->getManager();
        $em->remove($articulo);
        $em->flush();
        
        return $this->redirectToRoute('inicio');
    }
}
