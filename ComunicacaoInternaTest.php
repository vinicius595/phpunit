<?php
namespace ComunicacaoInternaTest\Entity\Repository;

use ComunicacaoInterna\Entity\ComunicacaoInterna as ComunicacaoInternaEntity;
use ComunicacaoInterna\Entity\Repository\ComunicacaoInterna as ComunicacaoInternaRepository;
use ApplicationTest\Application\Entity\EntityTestAbstract;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Zend\Paginator\Paginator;

/**
 * Class ComunicacaoInternaTest
 * @package ComunicacaoInternaTest\Entity\Repository
 */
class ComunicacaoInternaTest extends EntityTestAbstract
{

    /**
     * @var string
     */
    protected $_repositoryName = ComunicacaoInternaEntity::class;

    /**
     * @var ComunicacaoInternaRepository
     */
    private $repository;

    /**
     * @var \User\Entity\User
     */
    private $usuario;

    private $idCargo, $idUnidade, $idDepartamento, $idEmpresa;

    public function setUp()
    {
        if (!extension_loaded('uopz')) {
            $this->markTestSkipped('uopz não instalado');
        }

        parent::setUp();

        $this->usuario = $this->getIdentity();

        $this->idCargo = $this->usuario->getCargo()->getIdCargo();
        $this->idUnidade = $this->usuario->getUnidade()->getIdUnidade();
        $this->idDepartamento = $this->usuario->getDepartamento()->getIdDepartamento();
        $this->idEmpresa = $this->usuario->getIdEmpresaOriginal();

        $this->repository = $this->getEntityManager()->getRepository($this->_repositoryName);
    }

    public function testGetCaixaEntradaIds()
    {
        $results = $this->repository->getCaixaEntradaIds();
        $this->assertEquals(true, is_array($results));
    }

    public function testGetCaixaSaidaIds()
    {
        $results = $this->repository->getCaixaSaidaIds();
        $this->assertEquals(true, is_array($results));
    }

    public function testGetCaixaEntrada()
    {
        /** @var Query $query */
        $results = $this->repository->getCaixaEntrada([], false, 1, 20, true);
        $this->assertEquals(true, is_numeric($results));

        /** @var Query $query */
        $results = $this->repository->getCaixaEntrada(['array_result' => true], false, 1, 20, true);
        $this->assertEquals(true, is_numeric($results));

        /** @var Query $query */
        $results = $this->repository->getCaixaEntrada(['array_result' => true], false);
        $this->assertEquals(true, is_array($results));

        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        $today = new \DateTime();

        /** @var Query $query */
        $query = $this->repository->getCaixaEntrada([], false);

        $this->assertContains('stRespondidoDestino IN (0)', $query->getSQL());
        $this->assertContains("idUsuarioDestino = {$this->usuario->getId()}", $query->getSQL());
        $this->assertContains("dataInicio <= '{$today->format('Y-m-d H:i:s')}'", $query->getSQL());
        $this->assertContains('stCancelado = 0', $query->getSQL());
        $this->assertContains('stArquivado = 0', $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaEntrada([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_TODAS,
            'column' => 'criador',
            'direction' => 'ASC'
        ], false);

        $this->assertContains('full_name ASC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaEntrada([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_RESPONDIDAS,
            'column' => 'finalidade',
            'direction' => 'DESC'
        ], false);

        $this->assertContains('nmTitulo DESC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaEntrada([
            'column' => 'txAssunto',
            'direction' => 'DESC'
        ], false);

        $this->assertContains('txAssunto DESC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaEntrada([], true);
        $this->assertInstanceOf(Paginator::class, $query);
    }

    public function testGetCaixaSaida()
    {
        /** @var Query $query */
        $results = $this->repository->getCaixaSaida([], false, 1, 20, true);
        $this->assertEquals(true, is_numeric($results));

        /** @var Query $query */
        $results = $this->repository->getCaixaSaida(['array_result' => true], false, 1, 20, true);
        $this->assertEquals(true, is_numeric($results));

        /** @var Query $query */
        $results = $this->repository->getCaixaSaida(['array_result' => true], false);
        $this->assertEquals(true, is_array($results));

        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getCaixaSaida([], false);

        $this->assertContains('idUsuario = ?', $query->getSQL());
        $this->assertContains('stCancelado = 0', $query->getSQL());
        $this->assertContains('stArquivado = 0', $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaSaida([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_AGENDADOS,
            'column' => 'criador',
            'direction' => 'ASC'
        ], false);

        $this->assertcontains('dataInicio >= ?', $query->getSQL());
        $this->assertContains('full_name ASC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaSaida([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_FINALIZADAS,
            'column' => 'finalidade',
            'direction' => 'DESC'
        ], false);

        $this->assertcontains('stFinalizado = 1', $query->getSQL());
        $this->assertContains('nmTitulo DESC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaSaida([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_NAO_FINALIZADAS
        ], false);

        $this->assertcontains('stFinalizado = 0', $query->getSQL());
        $this->assertcontains('dataLimiteRetorno >= ?', $query->getSQL());
        $this->assertContains("jsonOpcoes, '$.stFinalizarComentarioResponsavel') IS NOT NULL", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaSaida([
            'column' => 'txAssunto',
            'direction' => 'DESC'
        ], false);

        $this->assertContains('txAssunto DESC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getCaixaSaida([], true);
        $this->assertInstanceOf(Paginator::class, $query);
    }

    public function testGetArquivos()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getArquivos([], false);

        $this->assertContains("idUsuarioDestino = {$this->usuario->getId()}", $query->getSQL());
        $this->assertContains('stCancelado = 1', $query->getSQL());
        $this->assertContains('stArquivado = 1', $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getArquivos([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_AGENDADOS,
            'column' => 'criador',
            'direction' => 'ASC'
        ], false);

        $this->assertContains('full_name ASC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getArquivos([
            'filter' => ComunicacaoInternaEntity::TP_FILTER_FINALIZADAS,
            'column' => 'finalidade',
            'direction' => 'DESC'
        ], false);

        $this->assertContains('nmTitulo DESC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getArquivos([
            'column' => 'txAssunto',
            'direction' => 'DESC'
        ], false);

        $this->assertContains('txAssunto DESC', $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->getArquivos([], true);
        $this->assertInstanceOf(Paginator::class, $query);
    }

    public function testTotalNuDestinoCopiaNaoLidas()
    {
        /** @var Query $query */
        $query = $this->repository->totalNuDestinoCopiaNaoLidas();
        $this->assertEquals(true, is_numeric($query));
    }

    public function testGetResponsaveisPorCi()
    {
        /** @var Query $query */
        $query = $this->repository->getResponsaveisPorCi();
        $this->assertEquals(true, is_array($query));

        uopz_set_return(AbstractQuery::class, 'getArrayResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getResponsaveisPorCi();

        $this->assertContains('inactive = 0', $query->getSQL());
        $this->assertContains('stCancelado = 0', $query->getSQL());
        $this->assertContains('stArquivado = 0', $query->getSQL());
        $this->assertContains('full_name ASC', $query->getSQL());
    }

    public function testGetComunicacaoInterno()
    {
        /** @var Query $query */
        $query = $this->repository->getComunicadoInterno(99999999999);
        $this->assertEquals(true, is_null($query));

        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getArquivos([], false);

        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());
    }

    public function testGetCisNaoRespondidas()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->getCisNaoRespondidas();

        $this->assertContains('idResposta IS NULL', $query->getSQL());
        $this->assertContains('stCancelado = 0', $query->getSQL());
        $this->assertContains('stArquivado = 0', $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());
    }

    public function testCiWithoutAnswer()
    {
        uopz_set_return(AbstractQuery::class, 'getResult', function() {
            /** @var Query $obj */
            $obj = $this;
            return $obj;
        }, true);

        /** @var Query $query */
        $query = $this->repository->ciWithoutAnswer();

        $this->assertContains("idUsuarioDestino = {$this->usuario->getId()}", $query->getSQL());
        $this->assertContains('stCancelado = 0', $query->getSQL());
        $this->assertContains('stArquivado = 0', $query->getSQL());
        $this->assertContains("idEmpresa = '{$this->idEmpresa}'", $query->getSQL());
        $this->assertContains("stAtivo = '1'", $query->getSQL());

        /** @var Query $query */
        $query = $this->repository->ciWithoutAnswer(true);
        $this->assertContains('idComunicacaoInterna NOT IN', $query->getSQL());
    }

}

