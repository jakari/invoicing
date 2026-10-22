package com.reskator.invoicing.invoice

/** Finnish payment reference numbers: a base followed by its 7-3-1 check digit. */
object ReferenceCounter {
    fun reference(base: Long): Long = "$base${checksum(base)}".toLong()

    /** The base after [reference]: the reference without its check digit, plus one. */
    fun nextBase(reference: Long): Long = reference / 10 + 1

    /** Weights 7, 3, 1 repeat from the rightmost digit; the check digit tops the sum up to the next ten. */
    fun checksum(base: Long): Int {
        val weights = intArrayOf(7, 3, 1)
        val total = base.toString().reversed().withIndex().sumOf { (i, digit) -> digit.digitToInt() * weights[i % 3] }

        return (10 - total % 10) % 10
    }
}
