
import {Injectable} from "angular2/core";
import {Invoice} from "./invoice.model";
import {Http} from "angular2/http";
import {Observable} from "rxjs/Observable";
import {Response} from "angular2/http";
import {CustomerModel} from "./customer.model";
import {Item} from "./item.model";

@Injectable()
export class InvoiceService {
    private http:Http;

    constructor(http:Http) {
        this.http = http;
    }

    public create(invoice:Invoice): Observable<Response>{
        return this.http.post('/api/invoices', JSON.stringify(invoice));
    }

    public getInvoices():Observable<Response> {
        return this.http.get('/api/invoices');
    }

    public getInvoice(id:number):Observable<Invoice> {
        return this.http.get('/api/invoice/'+id)
            .map((response:Response) :Invoice => {
                var data = response.json();
                var invoice = new Invoice();
                invoice.id = data.id;
                invoice.created = data.created;
                invoice.due = data.due;
                invoice.customer = new CustomerModel();
                invoice.customer.id = data.customer.id;
                invoice.customer.name = data.customer.name;
                invoice.customer.streetName = data.customer.streetName;
                invoice.customer.postCode = data.customer.postCode;
                invoice.customer.city = data.customer.city;
                invoice.customer.email = data.customer.email;
                invoice.invoiceNumber = data.invoiceNumber;
                invoice.referenceNumber = data.referenceNumber;
                invoice.items = data.items.map((data:any) => {
                    var item:Item = new Item();
                    item.amount = data.amount;
                    item.description = data.description;
                    item.price = data.price;
                    item.tax = data.tax;

                    return item;
                });

                return invoice;
            });
    }

    public update(invoice:Invoice):Observable<Response> {
        return this.http.patch('/api/invoice/'+invoice.id, JSON.stringify(invoice));
    }

    public remove(invoice:Invoice):Observable<Response> {
        return this.http.delete('/api/invoice/'+invoice.id);
    }
}
