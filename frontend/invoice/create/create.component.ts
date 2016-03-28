
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {InvoiceFormComponent} from "../invoice-form.component";

@Component({
    templateUrl: 'app/invoice/create/create.html',
    directives: [InvoiceFormComponent]
})
export class CreateInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;

    constructor(invoiceService:InvoiceService) {
        this.invoiceService = invoiceService;
        this.invoice = new Invoice();
        this.invoice.customer = 'Asiakas Oy';
    }

    create():void {
        // Todo
    }
}
