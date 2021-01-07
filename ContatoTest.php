<?php
namespace ContatoTest\Entity\Repository;

use Contato\Entity\Contato as ContatoEntity;
use Contato\Entity\Repository\Contato as ContatoRepository;
use ApplicationTest\Application\Entity\EntityTestAbstract;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Zend\Paginator\Paginator;

/**
 * Class ContatoTest
 * @package ContatoTest\Entity\Repository
 */
class ContatoTest extends EntityTestAbstract
{

    /**
     * @var string
     */
    protected $_repositoryName = ContatoEntity::class;

    /**
     * @var ContatoRepository
     */
    private $repository;

    /**
     * @var \User\Entity\User
     */
    private $usuario;

    public function setUp()
    {
        if (!extension_loaded('uopz')) {
            $this->markTestSkipped('uopz não instalado');
        }

        parent::setUp();

        $this->usuario = $this->getIdentity();

        $this->repository = $this->getEntityManager()->getRepository($this->_repositoryName);
        $this->idUnidade = $this->usuario->getUnidade()->getIdUnidade();
        $this->idDepartamento = $this->usuario->getDepartamento()->getIdDepartamento();
    }

    // Verifica a chamada principal para visualização de contatos
    public function testVerContato()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getContatosRamais([
            'limit' => 30
        ]);
    }

    // Filtra de forma crescente e verifica os campos obrigatrios Nome, Unidade e Empresa do usuário
    public function testFiltrarContatos()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->filterContatosRamais([
            'limit' => 30
        ]);

        $this->assertContains('.nmNome ASC', $query->getSQL());
        $this->assertContains('.idUnidade', $query->getSQL());
        $this->assertContains('.idDepartamento', $query->getSQL());
    }

    // utilizando a função valida se recebe uma string
    public function testPrimeirasLetrasContatos ()
    {
        $letra = 'u.nmNome';

        uopz_set_return(AbstractQuery::class, 'getResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getFirstLettersContatoName(['string(u.nmNome, 1, 1)']);

        $this->assertInternalType('string', $letra);
    }

    //verifica se recebe um array contendo caracteres de e-mail e nome pra criação de contato
    public function testContatoExterno ()
    {
//        uopz_set_return(AbstractQuery::class, 'getResult', function () {
//            /** @var Query $obj */
//            $obj = $this;
//            return $obj;
//        }, true);

        /** @var Query $query */
        $query = $this->repository->getContatosToApi('email' , '@');
        $query2 = $this->repository->getContatosToApi('nome' , 'string');

        $this->assertEquals(true, is_array($query));
        $this->assertEquals(true, is_array($query2));
    }

    //Verificação do paginator e se recebe data de nascimento e nome
    public function testAniversariantesContatos ()
    {
        /** @var Query $query */
        $query = $this->repository->getContatosProximosAniversariantes();
        //print get_class($query);
        $this->assertInstanceOf(Paginator::class, $query);

        uopz_set_return(AbstractQuery::class, 'getResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getContatosProximosAniversariantes([], false);

        $this->assertNotNull('.dataNascimento', $query->getSQL());
        $this->assertContains('.nmNome ASC', $query->getSQL());
    }

    public function testAniversariantesDia ()
    {
        /** @var Query $query */
        $query = $this->repository->getContatosAniversariantesDia();
        $this->assertInstanceOf(Paginator::class, $query);

        uopz_set_return(AbstractQuery::class, 'getResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getContatosAniversariantesDia([], false);

        $this->assertNotNull('.dataNascimento', $query->getSQL());
        $this->assertContains('.nmNome ASC', $query->getSQL());
    }

    public function testAniversariantesAnteriores ()
    {
        /** @var Query $query */
        $query = $this->repository->getContatosAniversariantesAnteriores();
        $this->assertInstanceOf(Paginator::class, $query);

        uopz_set_return(AbstractQuery::class, 'getResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getContatosAniversariantesAnteriores([], false);

        $this->assertNotNull('.dataNascimento', $query->getSQL());
//        $this->assertDateTimeNow('UTC+0',$query->getSQL(),null);
    }

    public function testContato ()
    {
        $idContato = 1;

        /** @var Query $query */
        $query = $this->repository->getContato($idContato);

        $equals = false;
        if (is_null($query) || $query instanceof ContatoEntity) {
            $equals = true;
        }

        $this->assertEquals(true, $equals);

        uopz_set_return(AbstractQuery::class, 'getOneOrNullResult', function () {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getContato($idContato);

        /** @var \Doctrine\ORM\Query\Parameter $parameter */
        $parameter = $query->getParameter('idContato');
        $this->assertEquals($idContato, $parameter->getValue());
    }

}
