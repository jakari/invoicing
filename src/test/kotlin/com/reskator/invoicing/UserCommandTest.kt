package com.reskator.invoicing

import com.github.ajalt.clikt.testing.test
import com.reskator.invoicing.auth.AuthService
import com.reskator.invoicing.auth.UserRepository
import com.reskator.invoicing.command.CreateUser
import com.reskator.invoicing.support.Fixtures
import com.reskator.invoicing.support.TestDatabase
import kotlin.test.BeforeTest
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertNotNull
import kotlin.test.assertNull

class UserCommandTest {
    private val users = UserRepository(TestDatabase.jdbi)

    @BeforeTest
    fun setUp() = TestDatabase.reset()

    @Test
    fun `creates a user that can log in`() {
        val result = CreateUser { users }.test("--username matti", stdin = "salasana\nsalasana\n", inputInteractive = true)

        assertEquals(0, result.statusCode, result.output)
        assertContains(result.output, "User matti created")
        assertNotNull(AuthService(users).authenticate("matti", "salasana"))
        assertNull(AuthService(users).authenticate("matti", "wrong"))
    }

    @Test
    fun `refuses an existing username`() {
        Fixtures.user("matti")

        val result = CreateUser { users }.test("--username matti", stdin = "salasana\nsalasana\n", inputInteractive = true)

        assertEquals(1, result.statusCode)
        assertContains(result.output, "already exists")
    }
}
