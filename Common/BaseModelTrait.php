<?php


namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\QueryException;

trait BaseModelTrait
{
    /**
     * @param $query
     * @param string $columnName
     * @param string|null $fromDate
     * @param string|null $toDate
     * @param bool $onlyDate
     * @return mixed
     */
    protected function makeSearchByDatetimeRage(
        $query,
        string $columnName,
        string $fromDate = null,
        string $toDate = null,
        bool $onlyDate = true
    ) {
        $function = "where";
        $format = DB_DATETIME_FORMAT;

        if ($onlyDate) {
            $function = "whereDate";
        }

        if (!empty($fromDate) && validateDateTime($fromDate)) {
            $fromDate = new Carbon($fromDate);
            $query->{$function}($columnName, '>=', $fromDate->format($format));
        }

        if (!empty($toDate) && validateDateTime($toDate)) {
            $fromDate = new Carbon($toDate);
            $query->{$function}($columnName, '<=', $fromDate->format($format));
        }

        return $query;
    }

    /**
     * Make order
     *
     * @param array $params
     * @param array $allowed
     * @param string $defaultSort
     * @param string $defaultOrder
     * @return mixed
     */
    public function makeOrder(
        $query,
        array $params,
        array $allowed = SORT_BY_ALLOWED,
        string $defaultSort = SORT_BY_DEFAULT,
        string $defaultOrder = SORT_ORDER_DEFAULT
    ) {
        $query->orderBy(
            $this->getSortColumn($params[REQUEST_SORT_BY] ?? null, $allowed, $defaultSort),
            $this->getSortOrder($params[REQUEST_SORT_ORDER] ?? null, $defaultOrder)
        );

        return $query;
    }

    /**
     * Get sort column
     *
     * @param string|null $column
     * @param array $listAllowed
     * @param string $default
     * @return string
     */
    protected function getSortColumn(?string $column, array $listAllowed, string $default = COLUMN_ID)
    {
        if (!empty($column)) {
            return in_array($column, $listAllowed) ? $column : $default;
        }

        return $default;
    }

    /**
     * Get sort order
     *
     * @param string|null $order
     * @param string $default
     * @return string
     */
    protected function getSortOrder(?string $order, $default = SORT_ORDER_DEFAULT)
    {
        if (!empty($order)) {
            return in_array($order, SORT_ORDER_ALLOWED) ? $order : $default;
        }

        return $default;
    }

    /**
     * Get limit rows
     *
     * @param int|null $limit
     * @return int
     */
    public function getLimitRows($limit = null)
    {
        $limit = $limit ?? request()->get(REQUEST_LIMIT);

        if (!is_null($limit) && is_numeric($limit) && $limit > 0) {
            return (int)$limit;
        }

        return PAGINATE_ROW;
    }

    /**
     * Get current page
     *
     * @return float|int
     */
    public function getPage()
    {
        if (!($page = request()->get(REQUEST_PAGE)) || !is_numeric($page) || $page < 1) {
            return 1;
        }

        return floor($page);
    }

    /**
     * Get offset for pagination
     *
     * @param int|null $limit
     * @return int
     */
    public function getPaginationOffset($limit = null)
    {
        return (int)floor(($this->getPage() - 1) * $this->getLimitRows($limit));
    }

    /**
     * Find by column
     *
     * @param array $column
     * @param bool $withTrashed
     * @return mixed
     */
    public function findByColumn(array $column, bool $withTrashed = false)
    {
        try {
            $query = $this->where([$column]);

            if ($withTrashed) {
                $query = $query->withTrashed();
            }

            return $query->first();
        } catch (QueryException $e) {
            return null;
        }
    }
}
