
import {Component} from 'angular2/core';
import {Invoice} from "../invoice.model";
import {InvoiceService} from "../invoice.service";
import {ROUTER_DIRECTIVES} from "angular2/router";
import {InvoiceListItem} from "../invoice.list.item.model";
import {Response} from "angular2/http";

@Component({
    templateUrl: 'app/invoice/list/list.html',
    directives: [ROUTER_DIRECTIVES]
})
export class ListInvoicesComponent {
    private invoiceService:InvoiceService;
    invoices:Array<InvoiceListItem> = [];

    constructor(invoiceService:InvoiceService) {
        this.invoiceService = invoiceService;
        this.invoiceService.getInvoices().subscribe((response:Response) => this.setInvoices(response.json()));
    }

    private setInvoices(invoices:any) {
        this.invoices = invoices.map((invoice:any) => {
            var model = new InvoiceListItem();
            model.id = invoice.id;
            model.customer = invoice.customer;
            model.created = new Date(invoice.created);
            model.due = new Date(invoice.due);
            model.referenceNumber = invoice.referenceNumber;
            model.invoiceNumber = invoice.invoiceNumber;
            model.total = invoice.total;

            return model;
        });
    }
}
