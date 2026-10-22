package com.reskator.invoicing.invoice

import kotlin.test.Test
import kotlin.test.assertEquals

class ReferenceCounterTest {
    @Test
    fun `appends the finnish 7-3-1 check digit`() {
        assertEquals(1009, ReferenceCounter.reference(100))
        assertEquals(1012, ReferenceCounter.reference(101))
        // weights from the right: 3·7 + 2·3 + 1·1 = 28 → 2
        assertEquals(1232, ReferenceCounter.reference(123))
        // 5·7 + 1·3 = 38 → 2
        assertEquals(152, ReferenceCounter.reference(15))
        // 1·7 + 2·3 + 3·1 + 4·7 = 44 → 6, the weights repeat
        assertEquals(43216, ReferenceCounter.reference(4321))
    }

    @Test
    fun `check digit is 0 when the weighted sum is a multiple of ten`() {
        // 2·7 + 2·3 = 20 → 0
        assertEquals(220, ReferenceCounter.reference(22))
    }

    @Test
    fun `the next base drops the check digit of the latest reference`() {
        assertEquals(101, ReferenceCounter.nextBase(1009))
        assertEquals(4322, ReferenceCounter.nextBase(43216))
    }
}
