
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {CustomerModel} from "../customer.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {Observable} from "rxjs/Observable";

@Component({
    templateUrl: 'app/invoice/create/create.html',
    directives: [InvoiceFormComponent]
})
export class CreateInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private customerSearch:String;

    constructor(invoiceService:InvoiceService) {
        this.invoiceService = invoiceService;
        this.invoice = new Invoice();
        this.invoice.customer = new CustomerModel();
    }

    create():void {
        this.invoiceService.create(this.invoice).subscribe(
            data => console.log(data)
        );
    }
}
