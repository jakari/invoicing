
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {RouteParams} from "angular2/router";

@Component({
    templateUrl: 'app/invoice/edit/edit.html',
    directives: [InvoiceFormComponent]
})
export class EditInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private id:number;

    constructor(invoiceService:InvoiceService, routeParams:RouteParams) {
        this.invoiceService = invoiceService;
        this.id = Number(routeParams.get('id'));

        this.invoice = new Invoice();
        this.invoice.customer = 'Asiakas Oy';
    }

    edit():void {
        // Todo
    }
}
