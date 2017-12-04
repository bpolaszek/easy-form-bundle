# Easy Form Bundle

Provides an easy integration of toggle / delete buttons in any entity grid, with csrf protection.

For personnal purposes for the moment.

## Documentation

Basically:
```twig
{% for book in books %}
    {{ form(createDeletionForm(book, { 'attr': { 'class': 'btn-link' } }, path('book_delete'))) }}
{% endfor %}
```

This will render something like this:

```twig
<form method="post" action="{{ path('book_delete', { 'book': book.id }) }}">
    <input type="hidden" name="class" value="AppBundle\Entity\Book" />
    <input type="hidden" name="id" value="{{ book.id }}" />
    <input type="hidden" name="_method" value="DELETE" />
    <input type="hidden" name="_csrf_token" value="{{ csrf_token }}" />
    <button type="submit" name="submit" class="btn-link">Delete</button>
</form>
```

Visually, with Bootstrap 3, it will be rendered as a link. When clicked, the form is submitted as if it were a `DELETE` request.

Now on the back-end side:

 ```php
namespace AppBundle\Action;

use BenTools\EasyFormBundle\Action\DeleteEntityAction;
use BenTools\EasyFormBundle\Form\FormException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class DeleteBookAction
{
    /**
     * @var DeleteBookAction
     */
    private $remover;

    /**
     * @var RouterInterface 
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    /**
     * DeleteBookAction constructor.
     * @param DeleteEntityAction $remover
     * @param RouterInterface    $router
     * @param Session            $session
     */
    public function __construct(
        DeleteEntityAction $remover,
        RouterInterface $router,
        Session $session
    )
    {
        $this->remover = $remover;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @Route("/books", methods={"DELETE"}, name="book_delete")
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $remover = $this->remover;
        try {
            $book = $remover->__invoke($request); // Takes care of retrieving the corresponding entity, remove it from persistence, and return it.
        } catch (FormException $e) {
            throw $e; // Removal was impossible, check $e->getForm()->getErrors().
        }
        $this->session->getFlashBag()->add('success', sprintf('Book %s was successfully removed.', $book->getName())); // $book is actually removed from database but the entity still lives until the end of the script
        return new RedirectResponse($this->router->generate('book_index'));
    }

}
 ```
 
 You can generate toggle / enable / disable forms the same way; dive into the code if you need it.
 
 Your entity must implement `Sylius\Component\Resource\Model\ToggleableInterface` and your project must require `sylius/resource ^1.0`.
 
 ## Installation
 
 ```bash
 composer require bentools/easy-form-bundle
 ```
 
 And add it to your Kernel.