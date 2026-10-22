package com.reskator.invoicing.invoice

import com.reskator.invoicing.customer.CustomerInput
import com.reskator.invoicing.customer.CustomerRepository
import com.reskator.invoicing.customer.CustomerService
import com.reskator.invoicing.settings.InvoiceSettings
import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.TestDatabase
import java.math.BigDecimal
import java.nio.file.Path
import java.time.LocalDate
import java.util.concurrent.Callable
import java.util.concurrent.CyclicBarrier
import java.util.concurrent.Executors
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertEquals

/** B6: invoice numbers were MAX() + 1 with no lock, so concurrent invoices got the same number. */
class InvoiceServiceConcurrencyTest {
    private val settings = InvoiceSettings(Path.of(javaClass.getResource("/settings/default-settings.yml")!!.toURI()).toString())
    private val repository = InvoiceRepository()
    private val service = InvoiceService(
        TestDatabase.jdbi, repository, CustomerService(TestDatabase.jdbi, CustomerRepository(), repository), ParameterService(settings)
    )
    private var company = 0

    private val input = InvoiceInput(
        created = LocalDate.of(2026, 1, 1),
        due = LocalDate.of(2026, 1, 15),
        customer = CustomerInput(null, "Asiakas Oy", "", "", "Katu 1", "00100", "Helsinki", "a@example.local"),
        items = listOf(ItemInput(null, "Työ", 1, Money(100), BigDecimal("25.5"))),
        remarkingTime = 14,
        interestOnArrears = 18,
        template = "finnish"
    )

    @BeforeTest
    fun setUp() {
        TestDatabase.reset()
        company = Fixtures.company("Company A")
    }

    @Test
    fun `concurrent invoices get their own numbers`() {
        val threads = 8
        val barrier = CyclicBarrier(threads)
        val pool = Executors.newFixedThreadPool(threads)

        val numbers = pool.invokeAll((1..threads).map { Callable { barrier.await(); service.create(company, input) } }).map { it.get() }
        pool.shutdown()

        assertEquals((1..threads).toList(), numbers.sorted())
    }
}
