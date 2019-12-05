<?php
namespace AppBundle\Controller;
// Include entity we create in our Controller file
use AppBundle\Entity\Todo;
use Symfony\Component\Routing\Annotation\Route;
#use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
// need some classes in Controller because we need that in our form (for inputs that we will create)
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TodoController extends Controller
{
    /**
     * @Route("/", name="todo_list")
     */
    public function listAction(Request $request) {
    // use getDoctrine to  use doctrine and we'll select the entity we want to work with, used findAll() to bring all information from it and we'll save it inside a variable named todos and type of the result will be an array
        $todos = $this->getDoctrine()->getRepository('AppBundle:Todo')->findAll();
        // replace this example code with whatever we need
        return $this->render('todo/index.html.twig', array('todos'=>$todos));
    }
    /**
     * @Route("/todo/create", name="todo_create")
     */
    public function createAction(Request $request) {
    // Here we create an object from the class we made
        $todo = new Todo;
    // Here we'll build a form using createFormBuilder and inside it we will put our object and we'll write add, then select the input type, then array to add attribute we want in input field
        $form = $this->createFormBuilder($todo)->add('name', TextType::class, array('attr' => array('class' => 'form-control mb-3')))
            ->add('category', TextType::class, array('attr' => array('class' => 'form-control mb-3')))
            ->add('description', TextareaType::class, array('attr' => array('class' => 'form-control mb-3')))
            ->add('priority', ChoiceType::class, array('choices' => array('Low' => 'Low', 'Normal' => 'Normal', 'High' => 'High'),'attr' => array('class' => 'form-control mb-3')))
            ->add('due_date', DateTimeType::class, array('attr' => array('class'=>'mb-3')))
            ->add('save', SubmitType::class, array('label' => 'Create Todo', 'attr' => array('class'=> 'btn-primary mb-3')))
            ->getForm();
        $form -> handleRequest($request);
    
    // Here we have an if statement -> if we click submit & if form is valid -> we'll take values from form and we'll save them in new variables
        if($form->isSubmitted() && $form->isValid()){
            // fetching data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();
            // getting current data
            $now = new\DateTime('now');
            // these functions we bring from entities, every column has a set function, we put value that we get from form
            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($due_date);
            $todo->setCreateDate($now);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();
            $this->addFlash(
                'notice',
                'Todo Added'
            );
            
            return $this->redirectToRoute('todo_list');
        }
    // now to make form we'll add line form->createView() and we'll see form in create.html.twig 
    return $this->render('todo/create.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/todo/details/{id}", name="todo_details")
     */
    public function detailsAction($id) {
        $todo = $this->getDoctrine()->getRepository('AppBundle:Todo')->find($id);
        return $this->render('todo/details.html.twig', array('todo' => $todo));
    }
    /**
     * @Route("/todo/edit/{id}", name="todo_edit")
     */
    public function editAction($id, Request $request) {
    // Here we have a variable todo, will save the result of this search and it will be one result, because we search based on a specific id
        $todo = $this->getDoctrine()->getRepository('AppBundle:Todo')->find($id);
        $now = new\DateTime('now');
    // now we'll use set functions and inside them we'll bring the value that already exists using get function, e.g. we have setName() and inside we'll bring its current value and use it again
        $todo->setName($todo->getName());
        $todo->setCategory($todo->getCategory());
        $todo->setDescription($todo->getDescription());
        $todo->setPriority($todo->getPriority());
        $todo->setDueDate($todo->getDueDate());
        $todo->setCreateDate($now);
    // now when we type createFormBuild and put variable todo, the form will be filled of data that is already set
        $form = $this->createFormBuilder($todo)->add('name', TextType::class, array('attr' => array('class'=> 'form-control mb-3')))
        ->add('category', TextType::class, array('attr' => array('class'=> 'form-control  mb-3')))
        ->add('description', TextareaType::class, array('attr' => array('class'=> 'form-control  mb-3')))
        ->add('priority', ChoiceType::class, array('choices'=>array('Low'=>'Low', 'Normal'=>'Normal', 'High'=>'High'),'attr' => array('class'=> 'form-control  mb-3')))
        ->add('due_date', DateTimeType::class, array('attr' => array('class'=>'mb-3')))
        ->add('save', SubmitType::class, array('label'=> 'Update Todo', 'attr' => array('class'=> 'btn-primary mb-3')))
        ->getForm();
    
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            //fetching data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $description = $form['description']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();
            $now = new\DateTime('now');
            $em = $this->getDoctrine()->getManager();
            $todo = $em->getRepository('AppBundle:Todo')->find($id);
            $todo->setName($name);
            $todo->setCategory($category);
            $todo->setDescription($description);
            $todo->setPriority($priority);
            $todo->setDueDate($due_date);
            $todo->setCreateDate($now);
        
            $em->flush();
            $this->addFlash(
                'notice',
                'Todo Updated'
                );
            return $this->redirectToRoute('todo_list');
            }
            return $this->render('todo/edit.html.twig', array('todo' => $todo, 'form' => $form->createView()));
        }
        /**
         * @Route("/todo/delete/{id}", name="todo_delete")
         */
        public function deleteAction($id){
            $em = $this->getDoctrine()->getManager();
            $todo = $em->getRepository('AppBundle:Todo')->find($id);
            $em->remove($todo);
            $em->flush();
            $this->addFlash(
                'notice',
                'Todo Removed'
            );
            return $this->redirectToRoute('todo_list');
        }
}
