<?php
namespace AvisoTest\Entity\Repository;

use Aviso\Entity\Aviso as AvisoEntity;
use Aviso\Entity\Repository\Aviso as AvisoRepository;
use ApplicationTest\Application\Entity\EntityTestAbstract;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Zend\Paginator\Paginator;

/**
 * Class AvisoTest
 * @package AvisoTest\Entity\Repository
 */
class AvisoTest extends EntityTestAbstract
{

    /**
     * @var string
     */
    protected $_repositoryName = AvisoEntity::class;

    /**
     * @var AvisoRepository
     */
    private $repository;

    /**
     * @var \User\Entity\User
     */
    private $usuario;

    private $idCargo, $idUnidade, $idDepartamento, $idEmpresa;

    public function setUp()
    {
        parent::setUp();

        $this->usuario = $this->getIdentity();

        $this->idCargo = $this->usuario->getCargo()->getIdCargo();
        $this->idUnidade = $this->usuario->getUnidade()->getIdUnidade();
        $this->idDepartamento = $this->usuario->getDepartamento()->getIdDepartamento();
        $this->idEmpresa = $this->usuario->getIdEmpresaOriginal();

        $this->repository = $this->getEntityManager()->getRepository($this->_repositoryName);
    }

    public function testGetAvisosHome()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getAvisosHome();

        $this->assertContains('dataInicio DESC', $query->getSQL());
        $this->assertContains("idCargo = {$this->idCargo}", $query->getSQL());
        $this->assertContains("idDepartamento = {$this->idDepartamento}", $query->getSQL());
        $this->assertContains("idUnidade = {$this->idUnidade}", $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());
    }

    public function testGetAvisosPublicados()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getAvisosPublicados();

        $this->assertContains('dataInicio DESC', $query->getSQL());
        $this->assertContains("idCargo = {$this->idCargo}", $query->getSQL());
        $this->assertContains("idDepartamento = {$this->idDepartamento}", $query->getSQL());
        $this->assertContains("idUnidade = {$this->idUnidade}", $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getAvisosPublicados(1);
        $this->assertContains('LIMIT 1', $query->getSQL());
    }

    public function testGetAvisos()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getAvisos([], false);

        $this->assertContains('dataCriacao DESC', $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getAvisos();
        $this->assertInstanceOf(Paginator::class, $query);
    }

    public function testGetAvisoPortal()
    {
        /** @var Query $query */
        $query = $this->repository->getAvisoPortal(1);
        $this->assertInstanceOf(AvisoEntity::class, $query);
    }

    public function testGetAvisosPortal()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getAvisosPortal(false, [], false);

        $this->assertContains('dataInicio DESC', $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $date = new \DateTime();
        $query = $this->repository->getAvisosPortal(true, [
            'modal' => true,
            'data' => $date->format('Y-m-d')
        ]);
        $this->assertInstanceOf(Paginator::class, $query);
    }

    public function testGetVisibilidade()
    {
        $method = $this->repository->getVisibilidade();
        $this->assertEquals(true, is_array($method));
    }

}

