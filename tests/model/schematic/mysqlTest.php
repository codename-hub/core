<?php

namespace codename\core\tests\model\schematic;

use codename\core\app;
use codename\core\database;
use codename\core\database\mysql;
use codename\core\sensitiveException;
use codename\core\tests\helper;
use codename\core\tests\model\abstractModelTest;
use Exception;
use ReflectionException;

class mysqlTest extends abstractModelTest
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSetConfigExplicitConnectionValid(): void
    {
        parent::testSetConfigExplicitConnectionValid();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSetConfigExplicitConnectionInvalid(): void
    {
        parent::testSetConfigExplicitConnectionInvalid();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSetConfigInvalidValues(): void
    {
        parent::testSetConfigInvalidValues();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testModelconfigInvalidWithoutCreatedAndModifiedField(): void
    {
        parent::testModelconfigInvalidWithoutCreatedAndModifiedField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testModelconfigInvalidWithoutModifiedField(): void
    {
        parent::testModelconfigInvalidWithoutModifiedField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDeleteWithoutArgsWillFail(): void
    {
        parent::testDeleteWithoutArgsWillFail();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testUpdateWithoutArgsWillFail(): void
    {
        parent::testUpdateWithoutArgsWillFail();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddCalculatedFieldExistsWillFail(): void
    {
        parent::testAddCalculatedFieldExistsWillFail();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testHideFieldSingle(): void
    {
        parent::testHideFieldSingle();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testHideFieldMultipleCommaTrim(): void
    {
        parent::testHideFieldMultipleCommaTrim();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testHideAllFieldsAddOne(): void
    {
        parent::testHideAllFieldsAddOne();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testHideAllFieldsAddMultiple(): void
    {
        parent::testHideAllFieldsAddMultiple();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldComplexEdgeCaseNoVfr(): void
    {
        parent::testAddFieldComplexEdgeCaseNoVfr();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldComplexEdgeCasePartialVfr(): void
    {
        parent::testAddFieldComplexEdgeCasePartialVfr();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldComplexEdgeCaseFullVfr(): void
    {
        parent::testAddFieldComplexEdgeCaseFullVfr();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldComplexEdgeCaseRegularFieldFullVfr(): void
    {
        parent::testAddFieldComplexEdgeCaseRegularFieldFullVfr();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldComplexEdgeCaseNestedRegularFieldFullVfr(): void
    {
        parent::testAddFieldComplexEdgeCaseNestedRegularFieldFullVfr();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldFailsWithNonexistingField(): void
    {
        parent::testAddFieldFailsWithNonexistingField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldFailsWithMultipleFieldsAndAliasProvided(): void
    {
        parent::testAddFieldFailsWithMultipleFieldsAndAliasProvided();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testHideAllFieldsAddAliasedField(): void
    {
        parent::testHideAllFieldsAddAliasedField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSimpleModelJoin(): void
    {
        parent::testSimpleModelJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSimpleModelJoinWithVirtualFields(): void
    {
        parent::testSimpleModelJoinWithVirtualFields();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testConditionalJoin(): void
    {
        parent::testConditionalJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testConditionalJoinFail(): void
    {
        parent::testConditionalJoinFail();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testReverseJoinEquality(): void
    {
        parent::testReverseJoinEquality();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testReplace(): void
    {
        parent::testReplace();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testMultiComponentForeignKeyJoin(): void
    {
        parent::testMultiComponentForeignKeyJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testTimemachineHistory(): void
    {
        parent::testTimemachineHistory();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDeleteSinglePkeyTimemachineEnabled(): void
    {
        parent::testDeleteSinglePkeyTimemachineEnabled();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testBulkUpdateAndDelete(): void
    {
        parent::testBulkUpdateAndDelete();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testBulkUpdateAndDeleteTimemachineEnabled(): void
    {
        parent::testBulkUpdateAndDeleteTimemachineEnabled();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testRecursiveModelJoin(): void
    {
        parent::testRecursiveModelJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSetRecursiveTwiceWillThrow(): void
    {
        parent::testSetRecursiveTwiceWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSetRecursiveInvalidConfigWillThrow(): void
    {
        parent::testSetRecursiveInvalidConfigWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSetRecursiveNonexistingFieldWillThrow(): void
    {
        parent::testSetRecursiveNonexistingFieldWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddRecursiveModelNonexistingFieldWillThrow(): void
    {
        parent::testAddRecursiveModelNonexistingFieldWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFiltercollectionValueArray(): void
    {
        parent::testFiltercollectionValueArray();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDefaultFiltercollectionValueArray(): void
    {
        parent::testDefaultFiltercollectionValueArray();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testLeftJoinForcedVirtualNoReferenceDataset(): void
    {
        parent::testLeftJoinForcedVirtualNoReferenceDataset();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testInnerJoinRegular(): void
    {
        parent::testInnerJoinRegular();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testInnerJoinForcedVirtualJoin(): void
    {
        parent::testInnerJoinForcedVirtualJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinVirtualFieldResultEnabledMissingVKey(): void
    {
        parent::testJoinVirtualFieldResultEnabledMissingVKey();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinVirtualFieldResultEnabledCustomVKey(): void
    {
        parent::testJoinVirtualFieldResultEnabledCustomVKey();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinHiddenFieldsNoVirtualFieldResult(): void
    {
        parent::testJoinHiddenFieldsNoVirtualFieldResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSameNamedCalculatedFieldsInVirtualFieldResults(): void
    {
        parent::testSameNamedCalculatedFieldsInVirtualFieldResults();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testRecursiveModelVirtualFieldDisabledWithAliasedFields(): void
    {
        parent::testRecursiveModelVirtualFieldDisabledWithAliasedFields();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSaveWithChildrenAuthoritativeDatasetsAndIdentifiers(): void
    {
        parent::testSaveWithChildrenAuthoritativeDatasetsAndIdentifiers();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testComplexVirtualRenormalizeForcedVirtualJoin(): void
    {
        parent::testComplexVirtualRenormalizeForcedVirtualJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testComplexVirtualRenormalizeRegular(): void
    {
        parent::testComplexVirtualRenormalizeRegular();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testComplexJoin(): void
    {
        parent::testComplexJoin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinNestingLimitExceededWillFail(): void
    {
        parent::testJoinNestingLimitExceededWillFail();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinNestingLimitMaxxedOut(): void
    {
        parent::testJoinNestingLimitMaxxedOut();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinNestingLimitMaxxedOutSaving(): void
    {
        parent::testJoinNestingLimitMaxxedOutSaving();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinNestingBypassLimitation1(): void
    {
        parent::testJoinNestingBypassLimitation1();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinNestingBypassLimitation2(): void
    {
        parent::testJoinNestingBypassLimitation2();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testJoinNestingBypassLimitation3(): void
    {
        parent::testJoinNestingBypassLimitation3();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGetCount(): void
    {
        parent::testGetCount();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFlags(): void
    {
        parent::testFlags();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFlagfieldValueNoFlagsInModel(): void
    {
        parent::testFlagfieldValueNoFlagsInModel();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFlagfieldValue(): void
    {
        parent::testFlagfieldValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGetFlagNonexisting(): void
    {
        parent::testGetFlagNonexisting();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testIsFlagNoFlagField(): void
    {
        parent::testIsFlagNoFlagField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFlagNormalization(): void
    {
        parent::testFlagNormalization();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddModelExplicitModelfieldValid(): void
    {
        parent::testAddModelExplicitModelfieldValid();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddModelExplicitModelfieldInvalid(): void
    {
        parent::testAddModelExplicitModelfieldInvalid();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddModelInvalidNoRelation(): void
    {
        parent::testAddModelInvalidNoRelation();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testVirtualFieldResultSaving(): void
    {
        parent::testVirtualFieldResultSaving();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testVirtualFieldResultCollectionHandling(): void
    {
        parent::testVirtualFieldResultCollectionHandling();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddCollectionModelMissingCollectionConfig(): void
    {
        parent::testAddCollectionModelMissingCollectionConfig();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddCollectionModelIncompatible(): void
    {
        parent::testAddCollectionModelIncompatible();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddCollectionModelInvalidModelField(): void
    {
        parent::testAddCollectionModelInvalidModelField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddCollectionModelValidModelFieldIncompatibleModel(): void
    {
        parent::testAddCollectionModelValidModelFieldIncompatibleModel();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGetNestedCollections(): void
    {
        parent::testGetNestedCollections();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testVirtualFieldResultSavingFailedAmbiguousJoins(): void
    {
        parent::testVirtualFieldResultSavingFailedAmbiguousJoins();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testVirtualFieldQuery(): void
    {
        parent::testVirtualFieldQuery();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testForcedVirtualJoinWithVirtualFieldResult(): void
    {
        parent::testForcedVirtualJoinWithVirtualFieldResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testForcedVirtualJoinWithoutVirtualFieldResult(): void
    {
        parent::testForcedVirtualJoinWithoutVirtualFieldResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testModelJoinWithJson(): void
    {
        parent::testModelJoinWithJson();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testInvalidFilterOperator(): void
    {
        parent::testInvalidFilterOperator();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testLikeFilters(): void
    {
        parent::testLikeFilters();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSuccessfulCreateAndDeleteTransaction(): void
    {
        parent::testSuccessfulCreateAndDeleteTransaction();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testTransactionUntrackedRunning(): void
    {
        parent::testTransactionUntrackedRunning();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testTransactionRolledBackPrematurely(): void
    {
        parent::testTransactionRolledBackPrematurely();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testNestedOrder(): void
    {
        parent::testNestedOrder();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testOrderLimitOffset(): void
    {
        parent::testOrderLimitOffset();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testLimitOffsetReset(): void
    {
        parent::testLimitOffsetReset();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddOrderOnNonexistingFieldWillThrow(): void
    {
        parent::testAddOrderOnNonexistingFieldWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testStructureData(): void
    {
        parent::testStructureData();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testStructureEncoding(): void
    {
        parent::testStructureEncoding();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGroupedGetCount(): void
    {
        parent::testGroupedGetCount();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGetCountAliasing(): void
    {
        parent::testGetCountAliasing();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddGroupOnCalculatedFieldDoesNotCrash(): void
    {
        parent::testAddGroupOnCalculatedFieldDoesNotCrash();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddGroupOnNestedCalculatedFieldDoesNotCrash(): void
    {
        parent::testAddGroupOnNestedCalculatedFieldDoesNotCrash();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddGroupNonExistingField(): void
    {
        parent::testAddGroupNonExistingField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAmbiguousAliasFieldsNormalization(): void
    {
        parent::testAmbiguousAliasFieldsNormalization();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateCount(): void
    {
        parent::testAggregateCount();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateCountDistinct(): void
    {
        parent::testAggregateCountDistinct();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddAggregateFieldDuplicateFixedFieldWillThrow(): void
    {
        parent::testAddAggregateFieldDuplicateFixedFieldWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddAggregateFieldSameNamedWithVirtualFieldResult(): void
    {
        parent::testAddAggregateFieldSameNamedWithVirtualFieldResult();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateSum(): void
    {
        parent::testAggregateSum();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateAvg(): void
    {
        parent::testAggregateAvg();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateMax(): void
    {
        parent::testAggregateMax();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateMin(): void
    {
        parent::testAggregateMin();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateDatetimeYear(): void
    {
        parent::testAggregateDatetimeYear();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateGroupedSumOrderByAggregateField(): void
    {
        parent::testAggregateGroupedSumOrderByAggregateField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateDatetimeQuarter(): void
    {
        parent::testAggregateDatetimeQuarter();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateDatetimeMonth(): void
    {
        parent::testAggregateDatetimeMonth();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateDatetimeDay(): void
    {
        parent::testAggregateDatetimeDay();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateFilterSimple(): void
    {
        parent::testAggregateFilterSimple();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateFilterValueArray(): void
    {
        parent::testAggregateFilterValueArray();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDefaultAggregateFilterValueArray(): void
    {
        parent::testDefaultAggregateFilterValueArray();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAggregateFilterValueArraySimple(): void
    {
        parent::testAggregateFilterValueArraySimple();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldAliasWithFilter(): void
    {
        parent::testFieldAliasWithFilter();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldAliasWithFilterArrayFallbackDataTypeSuccessful(): void
    {
        parent::testFieldAliasWithFilterArrayFallbackDataTypeSuccessful();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldAliasWithFilterArrayFallbackDataTypeFailsUnsupportedData(): void
    {
        parent::testFieldAliasWithFilterArrayFallbackDataTypeFailsUnsupportedData();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFilterWithEmptyArrayValue(): void
    {
        parent::testAddFilterWithEmptyArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFiltercollectionWithEmptyArrayValue(): void
    {
        parent::testAddFiltercollectionWithEmptyArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddDefaultfilterWithEmptyArrayValue(): void
    {
        parent::testAddDefaultfilterWithEmptyArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddDefaultFiltercollectionWithEmptyArrayValue(): void
    {
        parent::testAddDefaultFiltercollectionWithEmptyArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddAggregateFilterWithEmptyArrayValue(): void
    {
        parent::testAddAggregateFilterWithEmptyArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddDefaultAggregateFilterWithEmptyArrayValue(): void
    {
        parent::testAddDefaultAggregateFilterWithEmptyArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddDefaultfilterWithArrayValue(): void
    {
        parent::testAddDefaultfilterWithArrayValue();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFilterRootLevelNested(): void
    {
        parent::testAddFilterRootLevelNested();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFiltercollectionRootLevelNested(): void
    {
        parent::testAddFiltercollectionRootLevelNested();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldFilter(): void
    {
        parent::testAddFieldFilter();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldFilterNested(): void
    {
        parent::testAddFieldFilterNested();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAddFieldFilterWithInvalidOperator(): void
    {
        parent::testAddFieldFilterWithInvalidOperator();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDefaultfilterSimple(): void
    {
        parent::testDefaultfilterSimple();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAdhocDiscreteModelAsRoot(): void
    {
        parent::testAdhocDiscreteModelAsRoot();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testAdhocDiscreteModelComplex(): void
    {
        parent::testAdhocDiscreteModelComplex();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelLimitAndOffset(): void
    {
        parent::testDiscreteModelLimitAndOffset();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelAddOrder(): void
    {
        parent::testDiscreteModelAddOrder();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelSimpleAggregate(): void
    {
        parent::testDiscreteModelSimpleAggregate();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelSaveWillThrow(): void
    {
        parent::testDiscreteModelSaveWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelUpdateWillThrow(): void
    {
        parent::testDiscreteModelUpdateWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelReplaceWillThrow(): void
    {
        parent::testDiscreteModelReplaceWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testDiscreteModelDeleteWillThrow(): void
    {
        parent::testDiscreteModelDeleteWillThrow();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGroupAliasBugFixed(): void
    {
        parent::testGroupAliasBugFixed();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testNormalizeData(): void
    {
        parent::testNormalizeData();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testNormalizeDataComplex(): void
    {
        parent::testNormalizeDataComplex();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testValidateSimple(): void
    {
        parent::testValidateSimple();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testModelValidator(): void
    {
        parent::testModelValidator();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testModelValidatorSpecial(): void
    {
        parent::testModelValidatorSpecial();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testValidateSimpleRequiredField(): void
    {
        parent::testValidateSimpleRequiredField();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testValidateCollectionNotUsed(): void
    {
        parent::testValidateCollectionNotUsed();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testValidateCollectionData(): void
    {
        parent::testValidateCollectionData();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryFunctions(): void
    {
        parent::testEntryFunctions();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryFlags(): void
    {
        parent::testEntryFlags();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntrySetFlagNonexisting(): void
    {
        parent::testEntrySetFlagNonexisting();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntrySetFlagInvalidFlagValueThrows(): void
    {
        parent::testEntrySetFlagInvalidFlagValueThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryUnsetFlagInvalidFlagValueThrows(): void
    {
        parent::testEntryUnsetFlagInvalidFlagValueThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntrySetFlagNoDatasetLoadedThrows(): void
    {
        parent::testEntrySetFlagNoDatasetLoadedThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryUnsetFlagNoDatasetLoadedThrows(): void
    {
        parent::testEntryUnsetFlagNoDatasetLoadedThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntrySetFlagNoFlagsInModelThrows(): void
    {
        parent::testEntrySetFlagNoFlagsInModelThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryUnsetFlagNoFlagsInModelThrows(): void
    {
        parent::testEntryUnsetFlagNoFlagsInModelThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntrySaveNoDataThrows(): void
    {
        parent::testEntrySaveNoDataThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntrySaveEmptyDataThrows(): void
    {
        parent::testEntrySaveEmptyDataThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryUpdateEmptyDataThrows(): void
    {
        parent::testEntryUpdateEmptyDataThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryUpdateNoDatasetLoaded(): void
    {
        parent::testEntryUpdateNoDatasetLoaded();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryLoadNonexistingId(): void
    {
        parent::testEntryLoadNonexistingId();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testEntryDeleteNoDatasetLoadedThrows(): void
    {
        parent::testEntryDeleteNoDatasetLoadedThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldGetNonexistingThrows(): void
    {
        parent::testFieldGetNonexistingThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldGetNoDatasetLoadedThrows(): void
    {
        parent::testFieldGetNoDatasetLoadedThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldSetNonexistingThrows(): void
    {
        parent::testFieldSetNonexistingThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFieldSetNoDatasetLoadedThrows(): void
    {
        parent::testFieldSetNoDatasetLoadedThrows();
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testTimemachineDelta(): void
    {
        parent::testTimemachineDelta();
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // wait for rmysql to come up
        if (getenv('unittest_core_db_mysql_host')) {
            if (!helper::waitForIt(getenv('unittest_core_db_mysql_host'), 3306, 3, 3, 5)) {
                throw new Exception('Failed to connect to mysql server');
            }
        } else {
            static::markTestSkipped('Mysql host unavailable');
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        // shutdown the mysql server
        // At this point, we assume the DB data is stored on a volatile medium
        // to implicitly clear all data after shutdown

        // NOTE/WARNING: shutdown on the DB Server
        // will crash PDO connections that try to close themselves
        // after testing - STMT_CLOSE will fail due to passed-away DB
        // in a more or less random fashion.
        // As long as you use volatile containers, everything will be fine.
        // app::getDb('default')->query('SHUTDOWN;');

        parent::tearDownAfterClass();
    }

    /**
     * @return void
     * @throws \codename\core\exception
     * @throws sensitiveException
     */
    public function testConnectionErrorExceptionIsSensitive(): void
    {
        //
        // Important test to make sure a database error exception
        // doesn't leak any credentials (wrapped in a sensitiveException)
        //
        $this->expectException(sensitiveException::class);
        $this->getDatabaseInstance([
          'driver' => 'mysql',
          'host' => 'nonexisting-host',
          'user' => 'some-username',
          'pass' => 'some-password',
        ]);
    }

    /**
     * {@inheritDoc}
     * @param array $config
     * @return database
     * @throws \codename\core\exception
     * @throws sensitiveException
     */
    protected function getDatabaseInstance(array $config): database
    {
        return new mysql($config);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testInstanceClass(): void
    {
        static::assertInstanceOf(mysql::class, app::getDb());
    }

    /**
     * {@inheritDoc}
     */
    public function testAggregateDatetimeInvalid(): void
    {
        $this->expectExceptionMessage('EXCEPTION_MODEL_PLUGIN_CALCULATION_MYSQL_UNKNOWN_CALCULATION_TYPE');
        parent::testAggregateDatetimeInvalid();
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultDatabaseConfig(): array
    {
        return [
          'driver' => 'mysql',
          'host' => getenv('unittest_core_db_mysql_host'),
          'user' => getenv('unittest_core_db_mysql_user'),
          'pass' => getenv('unittest_core_db_mysql_pass'),
          'database' => getenv('unittest_core_db_mysql_database'),
          'autoconnect_database' => false,
          'port' => 3306,
          'charset' => 'utf8',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getJoinNestingLimit(): int
    {
        return 61;
    }
}
