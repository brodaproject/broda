Exemplos de uso
===============

Uso mais simples:

.. code-block:: php

    // controller
    function indexAction(Request $request)
    {
        // dados quaisquer de algum repositório
        $collection = /*...*/;

        $filter = AbstractFilter::detectFilterByRequest($request);

        return new RestResponse($rest->filter($collection, $filter));
    }


O ``$collection`` é qualquer coleção de dados em formato de ``array`` ou
que implemente a interface ``Doctrine\Common\Collections\Selectable``.
Além disso, também é suportado os ``QueryBuilder`` do ``Doctrine\ORM``
e do ``Doctrine\DBAL\Query``.
Com isso, a flexibilidade deste componente é imensa.

Exemplos de coleções suportadas:

.. code-block:: php

    // selectables
    $collection = $em->getRepository('User');

    $collection = $group->getUsers(); // ArrayCollection

    // querybuilders
    $collection = $em->createQueryBuilder()
                    ->select('u')->from('User', 'u');

    $collection = $dbal->createQueryBuilder()
                    ->select('u.id', 'u.name')->from('users', 'u');


Uso com QueryBuilders
---------------------

Com o uso de ``QueryBuilder`` (tanto do ``ORM`` quanto do ``DBAL``) você
garante a flexibilidade de filtragem dos registros.
Você pode, por exemplo, passar uma ``QueryBuilder`` já pré-filtrada,
que os filtros do lado cliente serão **incorporados** a ela automaticamente.

.. code-block:: php

    $qb = $orm->createQueryBuilder() // ou DBAL
                ->select('u')
                ->from('User', 'u')
                ->where('u.login = ?1')
                ->setParameter(1, 'john');

    $filter = /*...*/; // FilterInterface

    // aqui, os filtros do filter serão incorporados ao querybuider
    // com o operador AND
    return new RestResponse($rest->filter($qb, $filter));

Também há suporte para ``INNER JOINS`` nos ``QueryBuilder``. Porém, em alguns
casos, é necessário um ``fieldMap`` para mapear as colunas que não são da
tabela "root".

.. code-block:: php

    $qb = $orm->createQueryBuilder() // ou DBAL
                ->select('u')
                ->from('User', 'u')
                ->join('u.groups', 'g');

    $filter = /*...*/; // FilterInterface

    // aqui digo que se o filter pesquisar por 'login', ele irá
    // procurar no alias 'u', que é o usuário, e se pesquisar por 'name'
    // ele irá pesquisar no alias 'g', que são os grupos do usuário
    // se o campo não estiver mapeado, por padrão ele é direcionado
    // para o alias "root" (no caso, 'u')
    $fieldMap = array(
        'login' => 'u', // opcional
        'name' => 'g'
    );

    return new RestResponse($rest->filter($qb, $filter, $fieldMap));


Incorporators
-------------

Os ``QueryBuilder`` (e também os ``Selectable``) somente são filtrados
por causa dos ``Incorporator``, que são classes que são especialistas em
incorporar um tipo de collection e retornar os resultados filtrados da mesma.

Há 3 incorporators disponíveis por padrão, que são:

* ``SelectableIncorporator``: para classes que implementam o ``Selectable``
* ``DbalQueryBuilderIncorporator``: para o ``QueryBuilder`` do ``DBAL``
* ``OrmQueryBuilderIncorporator``: para o ``QueryBuilder`` do ``ORM``

Todos os incorporator estão no namespace: ``Broda\Component\Rest\Filter\Incorporator``.

Por padrão, os arrays são automaticamente convertidos para ``ArrayCollection``,
que por sua vez é tratado pelo ``SelectableIncorporator``.

Criando um Incorporator
-----------------------

Para criar um incorporator é simples. Você deve criar uma classe que implemente
``Broda\Component\Rest\Filter\Incorporator\IncorporatorInterface``, que tem
a seguinte assinatura:

.. code-block:: php

    interface IncorporatorInterface
    {
        public function incorporate($collection, FilterInterface $filter);

        public function count($collection, FilterInterface $filter);

        public static function supports($collection);
    }

Ou se preferir, pode simplesmente herdar de ``AbstractIncorporator``, disponível
no mesmo namespace.

Por definição, o método ``incorporate`` deve receber uma coleção qualquer
e um ``FilterInterface`` e retornar a mesma coleção, porém filtrada.
Para isso, você pode usar a lógica que quiser, desde que retorne uma coleção
filtrada. Geralmente, é retornado um ``ArrayCollection``, mas você pode
fazer o retorno como array. O importante é que o RestService consiga
tratar esse resultado no ``RestService::formatOutput``.

O suporte para o ``TotalizableInterface`` você mesmo deve dar no método
``incorporate``.

Exemplo de um Incorporator customizado
--------------------------------------

Abaixo um exemplo de como criar um incorporator e usá-lo na sua aplicação:

.. code-block:: php

    // AppModelIncorporator.php
    class AppModelIncorporator extends AbstractIncorporator
    {
        public function incorporate($collection, FilterInterface $filter)
        {
            /* @var $collection AppModel */
            $criterias = array();
            foreach ($filter->getColumnSearchs() as $colSearch) {
                $criterias[ $colSearch->getColumnName() ] = $colSearch->getTokens();
                // sem suporte para subcolunas, o exemplo aqui é bem básico
            }
            if ($gSearch = $filter->getGlobalSearch()) {
                // global search é em todas as colunas "pesquisáveis"
                foreach ($filter->getColumns() as $col) {
                    if (!$col->isSearchable()) continue;

                    if (!isset($criterias[ $col->getName() ]) {
                        $criterias[ $col->getName() ] = array();
                    }

                    $criterias[ $col->getName() ] = array_merge($criterias[ $col->getName() ], $$gSearch->getTokens());
                }
            }

            if ($offset = $filter->getFirstResult()) {
                $collection->setOffset($offset);
            }
            if ($limit = $filter->getMaxResults()) {
                $collection->setLimit($limit);
            }

            if ($orders = $filter->getOrderings()) {
                foreach ($orders as $order) {
                    $collection->addOrderBy($order->getColumn()->getName(), $order->getDir());
                }
            }

            return $collection->findByCriteria($criterias);

        }

        public function count($collection, FilterInterface $filter)
        {
            return $this->incorporate($collection, $filter)->getCount();
        }

        public static function supports($collection)
        {
            return ($collection instanceof AppModel);
        }
    }

    // index.php
    $rest->addIncorporator('AppModelIncorporator');

    $users = User::getCollection(); // retorna uma instancia AppModel

    $filter = /*...*/ // FilterInterface

    $restResponse = $this->rest->filter($users, $filter);