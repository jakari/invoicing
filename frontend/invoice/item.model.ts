
export class Item {
    public description:string;
    public amount:number = 1;
    public price:number;
    public tax:number = 24;

    public total():number {
        var price = this.price || 0;

        var tax = (this.tax / 100) + 1;

        return (this.amount * price) * tax;
    }
}
