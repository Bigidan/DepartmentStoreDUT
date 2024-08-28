import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from "@angular/common/http";
import {Observable} from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class DataService {
  private apiUrl = 'http://localhost/my-server/api.php';
  private apiDetailUrl = 'http://localhost/my-server/api_detail.php';
  private apiTableUrl = 'http://localhost/my-server/api_table.php';
  private apiSendDataUrl = 'http://localhost/my-server/api_send_data.php';

  constructor(private http: HttpClient) { }

  getData(mode: string, selectedNavItem: string, filters_a: any[] = [], filters_b: any[] = []): Observable<any> {
    let params = new HttpParams().set('mode', mode).set('tab', selectedNavItem);

    // Перетворимо масив фільтрів на параметри
    filters_a.forEach((filter, index) => {
      params = params.append(`filter_first_a[]`, filter);
    });
    filters_b.forEach((filter, index) => {
      params = params.append(`filter_second_a[]`, filter);
    });
    const queryString = params.toString();
    console.log('Сформований рядок запиту:', queryString);

    return this.http.get<any>(this.apiUrl, { params });
  }
  getItemData(selectedNavItem: string, selectedItemId: string) {
    let params = new HttpParams().set('selectedNavItem', selectedNavItem).set('selectedItemId', selectedItemId);
    return this.http.get<any>(this.apiDetailUrl, { params });
  }

  getColumnData(selectedNavItem: string) {
    let params = new HttpParams().set('selectedNavItem', selectedNavItem);
    return this.http.get<any>(this.apiTableUrl, { params });
  }
  sendData(data: any, content: string): Observable<any> {
    const payload = {
      transformedData: data,
      content: content
    };
    return this.http.post<any>(this.apiSendDataUrl, payload);
  }
}
