
import {Component} from 'angular2/core';
import {Invoice} from "../invoice.model";
import {InvoiceService} from "../invoice.service";
import {ROUTER_DIRECTIVES} from "angular2/router";

@Component({
    templateUrl: 'app/invoice/list/list.html',
    directives: [ROUTER_DIRECTIVES]
})
export class ListInvoicesComponent {
    private invoiceService:InvoiceService;
    invoices:Array<Invoice>;

    constructor(invoiceService:InvoiceService) {
        var invoice = new Invoice();
        invoice.id = 123;
        invoice.customer = 'Asiakas Oy';
        invoice.created = new Date('2015-11-10');
        invoice.due = new Date('2015-11-20');
        invoice.invoiceNumber = 123;
        invoice.referenceNumber = 1234;

        this.invoices = [invoice];
        this.invoiceService = invoiceService;
        // this.invoiceService.getInvoices();
    }
}
