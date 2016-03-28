
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {RouteParams} from "angular2/router";
import {Item} from "../item.model";
import {ROUTER_DIRECTIVES} from "angular2/router";

@Component({
    templateUrl: 'app/invoice/view/view.html',
    directives: [ROUTER_DIRECTIVES]
})
export class ViewInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private id:number;

    constructor(invoiceService:InvoiceService, routeParams:RouteParams) {
        this.invoiceService = invoiceService;
        this.id = Number(routeParams.get('id'));

        this.invoice = new Invoice();
        this.invoice.id = 123;
        this.invoice.customer = 'Asiakas Oy';
        this.invoice.created = new Date('2015-11-10');
        this.invoice.due = new Date('2015-11-20');
        this.invoice.invoiceNumber = 123;
        this.invoice.referenceNumber = 1234;

        var item = new Item();
        item.description = 'Tuote 1';
        item.price = 1234;
        item.tax = 24;
        item.amount = 1;

        this.invoice.items = [item];
    }

    edit():void {
        // Todo
    }
}
