## Taxonomy exercise
Standard laravel app with a tiny API to manage nodes in order to model company structure.

### Developing
`composer install` to get dependencies. 
`composer run format` for php-cs-fixer.
`php artisan test` to run tests.

The exercise was made in a TDD fashion. If the project had been bigger, I would have opted to create a `src` folder for a DDD-style structure for the domain specific code and left `app` for just the HTTP layer.

**Further work;** Given the project is an API, and obviously useful addition would be some sort of UI to monitor the API and it's usage and possibly also some simplistic reading of the data in the system.

#### Exercise
We in Clio, need to model how our company is structured so we can help our new employees have a better overview of our company structure.

We have our root node (only one, in our case the CEO) and several child nodes. Each of these nodes may have its own children.

It can be structured as something like this:
```

        root

       /    \

      a      b

      |

      c

    /   \

   d     e

```

**We need 3 endpoints that will serve basic operations:**
- Add a new node to the tree.
- Get all child nodes of a given node from the tree. (Just 1 layer of children)
- Change the parent node of a given node.


**Each node should have the following data:**
- Node identifier.
- node name.
- who is the parent node.
- The height of the node. (in the example above height(root)=0 and height(a)=1)
- Managers should have an extra field specifying the name of the department they are managing.
- Developers should have an extra field specifying the name of the programming language they are strongest in.
