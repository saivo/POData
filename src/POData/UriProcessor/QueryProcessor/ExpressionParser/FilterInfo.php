<?php

declare(strict_types=1);

namespace POData\UriProcessor\QueryProcessor\ExpressionParser;

/**
 * Class FilterInfo.
 */
class FilterInfo
{
    /**
     * Collection of navigation properties specified in the filter
     * clause, if no navigation (resource reference) property used
     * in the clause then this property will be null.
     *
     * e.g. $filter=NaviProp1/NaviProp2/PrimitiveProp eq 12
     *      $filter=NaviPropA/NaviPropB/PrimitiveProp gt 56.3
     * In this case array will be as follows:
     * array(array(NaviProp1, NaviProp2), array(NaviPropA, NaviPropB))
     *
     * @var array<array>|null
     */
    private $navigationPropertiesUsedInTheFilterClause;

    /**
     * The translated expression based on the Expression provider, if the end developer
     * opt for IDSQP2 then he is responsible for implementing IExpressionProvider
     * in this case this member variable will hold the expression generated through
     * custom ExpressionProvider, if user opt for IDSQP then the default
     * PHPExpressionProvider will be used, in this case this member variable will
     * hold the PHP expression generated through the PHPExpressionProvider.
     *
     * @var string
     */
    private $filterExpressionAsDataSourceExpression;

    /**
     * @param array  $navigationProperties     navigation properties in the $filter clause
     * @param string $filterExpAsDataSourceExp The $filter expression, specific to data source
     */
    public function __construct(array $navigationProperties, string $filterExpAsDataSourceExp)
    {
        $this->navigationPropertiesUsedInTheFilterClause = $navigationProperties;
        $this->filterExpressionAsDataSourceExpression    = $filterExpAsDataSourceExp;
    }

    /**
     * @return array|null
     */
    public function getNavigationPropertiesUsed(): ?array
    {
        return $this->navigationPropertiesUsedInTheFilterClause;
    }

    /**
     * Gets the data source specific expression as string.
     *
     * @return string
     */
    public function getExpressionAsString(): ?string
    {
        return $this->filterExpressionAsDataSourceExpression;
    }
}
