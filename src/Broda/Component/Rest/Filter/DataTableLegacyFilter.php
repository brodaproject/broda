<?php

namespace Broda\Component\Rest\Filter;

/**
 * Filtro para DataTables para versões 1.9 ou inferior.
 *
 * @link http://legacy.datatables.net/
 * @author raphael
 */
class DataTableLegacyFilter extends DataTableFilter
{

    /**
     * {@inheritDoc}
     */
    protected $ajaxSrc = 'aaData';

    /**
     * {@inheritDoc}
     */
    public function __construct(array $request)
    {
        $normalizedRequest = $this->normalizeRequestData($request);
        parent::__construct($normalizedRequest);
    }

    /**
     * {@inheritDoc}
     */
    public function getOutputResponse($output)
    {
        $a = $this->ajaxSrc;
        return array(
            'sEcho' => (int)$this->params['draw'],
            'iTotalRecords' => $this->totalRecords,
            'iTotalDisplayRecords' => $this->totalFiltered,
            $a => $output
        );
    }

    /**
     * Retorna TRUE se o request for de um DataTables 1.9 ou inferior
     *
     * @param array $request
     * @return bool
     */
    public static function isDataTableLegacy($request)
    {
        $request = self::fixRequestData($request);
        return isset($request['sEcho']);
    }

    /**
     * Arruma um problema de compatibilidade entre o Datatables 1.9
     * que envia os dados num formato [ 0 => { name: key, value: val }, ...]
     * em vez de { key: val, ... }
     *
     * @param array $data
     * @return array
     */
    private static function fixRequestData($data)
    {
        // arruma o array do datatables, que vem como objeto
        // com ->name e ->value em vez de array(name => value)
        $fixed = array();
        foreach ($data as $i => $o) {
            if (is_numeric($i)) {
                // datatables 1.9.4
                // é necessário converter pois algumas versões do dt
                // passam um objeto em vez de um array (bug)
                if (is_array($o)) {
                    $o = (object) $o;
                }
                $fixed[$o->name] = $o->value;

            } else {
                // datatables 1.10 em modo de compatibilidade
                $fixed[$i] = $o;
            }
        }
        return $fixed;
    }

    /**
     * Transforma os parametros do request do Datatables 1.9 em parametros
     * da versão >=1.10.
     *
     * @param array $data
     * @return array
     */
    private function normalizeRequestData(array $data)
    {
        // arruma o array do datatables, que vem como objeto
        // com ->name e ->value em vez de array(name => value)
        $normalized = self::fixRequestData($data);

        // make compatible with 1.10 params
        $normalized['draw'] = $normalized['sEcho'];
        $normalized['start'] = $normalized['iDisplayStart'];
        $normalized['length'] = $normalized['iDisplayLength'];

        $normalized['search'] = array(
            'value' => $normalized['sSearch'],
            'regex' => $normalized['bRegex'],
        );
        $normalized['columns'] = $this->getDtColumns($normalized);
        $normalized['order'] = $this->getDtOrders($normalized);

        return $normalized;
    }

    /**
     * Auxiliar para normalizar os orders
     *
     * @internal
     * @param array $data
     * @return array
     */
    private function getDtOrders(array $data)
    {
        $orders = array();
        $i=0;
        while ($data['sSortDir_'.$i]) {
            $orders[$i] = array(
                'column' => $data['iSortCol_'.$i],
                'dir' => $data['sSortDir_'.$i],
            );
            ++$i;
        }
        return $orders;
    }

    /**
     * Auxiliar para normalizar os columns
     *
     * @internal
     * @param array $data
     * @return array
     */
    private function getDtColumns(array $data)
    {
        $cols = array();
        $i=0;

        // names comes in format: name1,name2,name3...
        $names = explode(',', $data['sColumns']);

        while ($i < $data['iColumns']) {
            $cols[$i] = array(
                'data' => $data['mDataProp_'.$i],
                'name' => $names[$i],
                'searchable' => $data['bSearchable_'.$i],
                'orderable' => $data['bSortable_'.$i],
                'search' => array(
                    'value' => $data['sSearch_'.$i],
                    'regex' => $data['bRegex_'.$i],
                ),
            );
            ++$i;
        }
        return $cols;
    }


}
